<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\OwnerRequestsOrderCancellation;
use App\Chron\Model\Customer\Service\CustomerManagement;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\Repository\OrderList;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.async.default',
    handles: OwnerRequestsOrderCancellation::class,
)]
final readonly class CustomerRequestsOrderCancellationHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerManagement $customers,
        private InventoryReservationService $reservationService,
    ) {
    }

    public function __invoke(OwnerRequestsOrderCancellation $command): void
    {
        $order = $this->checkOrderContext($command->orderId(), $command->orderOwner());

        $order->cancelByOwner($this->reservationService);

        $this->orders->save($order);
    }

    private function checkOrderContext(OrderId $orderId, OrderOwner $orderOwner): Order
    {
        if (! $this->customers->exists($orderOwner->toString())) {
            throw OrderNotFound::withOrderOwner($orderOwner, $orderId);
        }

        $order = $this->orders->get($orderId);

        if (! $order instanceof Order) {
            throw OrderNotFound::withId($orderId);
        }

        return $order;
    }
}
