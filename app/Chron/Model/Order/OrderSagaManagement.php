<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Command\Order\CancelOrder;
use App\Chron\Application\Messaging\Command\Order\CloseOrder;
use App\Chron\Application\Messaging\Command\Order\CreateOrder;
use App\Chron\Application\Messaging\Command\Order\DeliverOrder;
use App\Chron\Application\Messaging\Command\Order\ModifyOrder;
use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Application\Messaging\Command\Order\RefundOrder;
use App\Chron\Application\Messaging\Command\Order\ReturnOrder;
use App\Chron\Application\Messaging\Command\Order\ShipOrder;
use App\Chron\Infrastructure\Service\CustomerOrderProvider;
use App\Chron\Package\Reporter\Report;
use RuntimeException;
use stdClass;
use Symfony\Component\Uid\Uuid;

use function random_int;
use function sprintf;

final readonly class OrderSagaManagement
{
    public function __construct(private CustomerOrderProvider $customerOrderProvider)
    {
    }

    public function processOrder(string $customerId): void
    {
        $currentOrder = $this->findCurrentOrderOfCustomer($customerId);

        if ($currentOrder === null) {
            $this->createOrder($customerId);

            return;
        }

        $orderStatus = OrderStatus::from($currentOrder->order_status);
        $orderId = $currentOrder->order_id;

        if ($orderStatus === OrderStatus::CREATED || $orderStatus === OrderStatus::MODIFIED) {
            $this->processPendingOrders($customerId, $orderId, $orderStatus);
        } else {
            $this->processCompletingOrders($customerId, $orderId, $orderStatus);
        }
    }

    public function shipPaidOrders(): int
    {
        $orders = $this->customerOrderProvider->findOrdersByStatus(OrderStatus::PAID);

        foreach ($orders as $order) {
            $this->shipOrder($order->customer_id, $order->order_id);
        }

        return $orders->count();
    }

    public function deliverShippedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::SHIPPED)
            ->each(fn (stdClass $order) => $this->deliverOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function returnDeliveredOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::DELIVERED)
            ->each(fn (stdClass $order) => $this->returnOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function refundReturnedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::RETURNED)
            ->each(fn (stdClass $order) => $this->refundOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function closeCancelledOrRefundedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findCancelledOrRefundedOrders()
            ->each(fn (stdClass $order) => $this->closeOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function closeOverdueDeliveredOrder(): int
    {
        $orders = $this->customerOrderProvider
            ->findOverdueDeliveredOrders()
            ->each(fn (stdClass $order) => $this->closeOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    private function processPendingOrders(string $customerId, string $orderId, OrderStatus $orderStatus): void
    {
        $lottery = $this->randomInt();

        if ($lottery < 5) {
            if ($orderStatus === OrderStatus::MODIFIED) {
                $this->cancelOrder($customerId, $orderId);
                $this->createOrder($customerId);
            }
        } elseif ($lottery < 20 && $orderStatus === OrderStatus::MODIFIED) {
            $this->payOrder($customerId, $orderId);
        } else {
            $this->modifyOrder($customerId, $orderId);
        }
    }

    private function processCompletingOrders(string $customerId, string $orderId, OrderStatus $orderStatus): void
    {
        switch ($orderStatus) {
            case OrderStatus::PAID:
                $this->shipOrder($customerId, $orderId);
                $this->createOrder($customerId);

                break;
            case OrderStatus::SHIPPED:
                $this->deliverOrder($customerId, $orderId);

                break;
            case OrderStatus::DELIVERED:
                $this->returnOrder($customerId, $orderId);

                break;
            case OrderStatus::RETURNED:
                $this->refundOrder($customerId, $orderId);

                break;
            case OrderStatus::REFUNDED:
            case OrderStatus::CANCELLED:
            case OrderStatus::CLOSED:
                break;
            default:
                throw new RuntimeException(sprintf('Unknown order status: %s', $orderStatus->value));
        }
    }

    private function createOrder(string $customerId): void
    {
        Report::relay(CreateOrder::forCustomer($customerId, Uuid::v4()->jsonSerialize()));
    }

    private function shipOrder(string $customerId, string $orderId): void
    {
        Report::relay(ShipOrder::forCustomer($customerId, $orderId));
    }

    private function deliverOrder(string $customerId, string $orderId): void
    {
        Report::relay(DeliverOrder::forCustomer($customerId, $orderId));
    }

    private function returnOrder(string $customerId, string $orderId): void
    {
        if ($this->randomInt() > 98) {
            Report::relay(ReturnOrder::forCustomer($customerId, $orderId));
        }
    }

    private function refundOrder(string $customerId, string $orderId): void
    {
        Report::relay(RefundOrder::forCustomer($customerId, $orderId));
    }

    private function cancelOrder(string $customerId, string $orderId): void
    {
        Report::relay(CancelOrder::forCustomer($customerId, $orderId));
    }

    private function modifyOrder(string $customerId, string $orderId): void
    {
        Report::relay(ModifyOrder::forCustomer($customerId, $orderId, $this->randomAmount()));
    }

    private function payOrder(string $customerId, string $orderId): void
    {
        Report::relay(PayOrder::forCustomer($customerId, $orderId));
    }

    private function closeOrder(string $customerId, string $orderId): void
    {
        Report::relay(CloseOrder::forCustomer($customerId, $orderId));
    }

    private function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        return $this->customerOrderProvider->findCurrentOrderOfCustomer($customerId);
    }

    private function randomInt(): int
    {
        return random_int(1, 100);
    }

    private function randomAmount(): string
    {
        return (string) fake()->randomFloat(2, 10, 3000);
    }
}
