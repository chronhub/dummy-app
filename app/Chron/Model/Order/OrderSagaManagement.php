<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Command\Order\CancelOrder;
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

    public function process(string $customerId): void
    {
        $currentOrder = $this->findCurrentOrderOfCustomer($customerId);

        if ($currentOrder === null) {
            $this->createOrder($customerId);

            return;
        }

        $orderStatus = OrderStatus::from($currentOrder->order_status);
        $orderId = $currentOrder->order_id;
        $customerId = $currentOrder->customer_id;

        if ($orderStatus->isPending()) {
            switch ($lottery = $this->randomInt()) {
                case $lottery < 10:
                    $this->cancelOrder($customerId, $orderId);

                    // create new order
                    break;
                case $lottery < 20:
                    $this->payOrder($customerId, $orderId);

                    break;
                default:
                    $this->modifyOrder($customerId, $orderId);

                    break;
            }
        }

        switch ($orderStatus) {
            case OrderStatus::PAID:
                $this->shipOrder($customerId, $orderId);

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
        if ($this->randomInt() < 95) {
            return;
        }

        Report::relay(ReturnOrder::forCustomer($customerId, $orderId));
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
        Report::relay(ModifyOrder::forCustomer($customerId, $orderId));
    }

    private function payOrder(string $customerId, string $orderId): void
    {
        Report::relay(PayOrder::forCustomer($customerId, $orderId));
    }

    private function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        return $this->customerOrderProvider->findCurrentOrderOfCustomer($customerId);
    }

    private function randomInt(): int
    {
        return random_int(1, 100);
    }
}
