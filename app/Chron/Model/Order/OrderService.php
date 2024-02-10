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
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\Uid\Uuid;

final readonly class OrderService
{
    use QueryPromiseTrait;

    public function __construct(private CustomerOrderProvider $customerOrderProvider)
    {
    }

    public function createOrder(string $customerId): void
    {
        Report::relay(CreateOrder::forCustomer($customerId, Uuid::v4()->jsonSerialize()));
    }

    public function shipOrder(string $customerId, string $orderId): void
    {
        Report::relay(ShipOrder::forCustomer($customerId, $orderId));
    }

    public function deliverOrder(string $customerId, string $orderId): void
    {
        Report::relay(DeliverOrder::forCustomer($customerId, $orderId));
    }

    public function returnOrder(string $customerId, string $orderId): void
    {
        if (fake()->numberBetween(1, 100) > 98) {
            Report::relay(ReturnOrder::forCustomer($customerId, $orderId));
        }
    }

    public function refundOrder(string $customerId, string $orderId): void
    {
        Report::relay(RefundOrder::forCustomer($customerId, $orderId));
    }

    public function cancelOrder(string $customerId, string $orderId): void
    {
        Report::relay(CancelOrder::forCustomer($customerId, $orderId));
    }

    public function modifyOrder(string $customerId, string $orderId): void
    {
        $amount = (string) fake()->randomFloat(2, 10, 3000);

        Report::relay(ModifyOrder::forCustomer($customerId, $orderId, $amount));
    }

    public function payOrder(string $customerId, string $orderId): void
    {
        Report::relay(PayOrder::forCustomer($customerId, $orderId));
    }

    public function closeOrder(string $customerId, string $orderId): void
    {
        Report::relay(CloseOrder::forCustomer($customerId, $orderId));
    }
}
