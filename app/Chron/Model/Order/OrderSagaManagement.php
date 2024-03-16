<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Service\OrderApplicationService;
use App\Chron\Infrastructure\Service\CustomerOrderProvider;
use RuntimeException;
use stdClass;

use function sprintf;

final readonly class OrderSagaManagement
{
    public function __construct(
        private OrderApplicationService $orderService,
        private CustomerOrderProvider $customerOrderProvider
    ) {
    }

    public function processOrder(string $customerId): void
    {
        $currentOrder = $this->customerOrderProvider->findCurrentOrderOfCustomer($customerId);

        if ($currentOrder === null) {
            $this->orderService->createOrder($customerId);

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
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::PAID)
            ->each(fn (stdClass $order) => $this->orderService->shipOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function deliverShippedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::SHIPPED)
            ->each(fn (stdClass $order) => $this->orderService->deliverOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function returnDeliveredOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::DELIVERED)
            ->each(fn (stdClass $order) => $this->orderService->returnOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function refundReturnedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findOrdersByStatus(OrderStatus::RETURNED)
            ->each(fn (stdClass $order) => $this->orderService->refundOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function closeCancelledOrRefundedOrders(): int
    {
        $orders = $this->customerOrderProvider
            ->findCancelledOrRefundedOrders()
            ->each(fn (stdClass $order) => $this->orderService->closeOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    public function closeOverdueDeliveredOrder(): int
    {
        $orders = $this->customerOrderProvider
            ->findOverdueDeliveredOrders()
            ->each(fn (stdClass $order) => $this->orderService->closeOrder($order->customer_id, $order->order_id));

        return $orders->count();
    }

    private function processPendingOrders(string $customerId, string $orderId, OrderStatus $orderStatus): void
    {
        $lottery = fake()->numberBetween(1, 100);

        if ($lottery < 5) {
            if ($orderStatus === OrderStatus::MODIFIED) {
                $this->orderService->cancelOrder($customerId, $orderId);
                $this->orderService->createOrder($customerId);
            }
        } elseif ($lottery < 40 && $orderStatus === OrderStatus::MODIFIED) {
            $this->orderService->payOrder($customerId, $orderId);
        } else {
            $this->orderService->modifyOrder($customerId, $orderId);
        }
    }

    private function processCompletingOrders(string $customerId, string $orderId, OrderStatus $orderStatus): void
    {
        switch ($orderStatus) {
            case OrderStatus::PAID:
                $this->orderService->shipOrder($customerId, $orderId);
                $this->orderService->createOrder($customerId);

                break;
            case OrderStatus::SHIPPED:
                $this->orderService->deliverOrder($customerId, $orderId);

                break;
            case OrderStatus::DELIVERED:
                $this->orderService->returnOrder($customerId, $orderId);

                break;
            case OrderStatus::RETURNED:
                $this->orderService->refundOrder($customerId, $orderId);

                break;
            case OrderStatus::REFUNDED:
            case OrderStatus::CANCELED:
            case OrderStatus::CLOSED:
                break;
            default:
                throw new RuntimeException(sprintf('Unknown order status: %s', $orderStatus->value));
        }
    }
}
