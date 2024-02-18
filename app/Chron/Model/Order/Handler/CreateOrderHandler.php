<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\CreateOrder;
use App\Chron\Model\Customer\Service\CustomerManagement;
use App\Chron\Model\Order\Exception\OrderAlreadyExists;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Model\Order\Service\UniqueOwnerPendingOrder;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CreateOrder::class,
)]
final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerManagement $customers,
        private UniqueOwnerPendingOrder $customerOrder
    ) {
    }

    public function __invoke(CreateOrder $command): void
    {
        $orderId = $command->orderId();
        $orderOwner = $command->orderOwner();

        $this->checkOrderContext($orderId, $orderOwner);

        $order = Order::create($orderId, $orderOwner);

        $this->orders->save($order);
    }

    private function checkOrderContext(OrderId $orderId, OrderOwner $orderOwner): void
    {
        if (! $this->customers->exists($orderOwner->toString())) {
            throw OrderNotFound::withOrderOwner($orderOwner, $orderId);
        }

        if ($this->customerOrder->hasPendingOrder($orderOwner->toString())) {
            throw OrderAlreadyExists::withPendingOrder($orderOwner);
        }

        if ($this->orders->get($orderId) !== null) {
            throw OrderAlreadyExists::withId($orderId);
        }
    }
}
