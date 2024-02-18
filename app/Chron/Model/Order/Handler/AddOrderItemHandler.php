<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: AddOrderItem::class
)]
final readonly class AddOrderItemHandler
{
    public function __construct(
        private OrderList $orders,
        private InventoryReservationService $inventoryReservationService,
    ) {
    }

    public function __invoke(AddOrderItem $command): void
    {
        $orderId = $command->orderId();

        $order = $this->orders->get($orderId);

        if (! $order instanceof Order) {
            throw OrderNotFound::withId($orderId);
        }

        $order->addOrderItem($command->orderItem(), $this->inventoryReservationService);

        $this->orders->save($order);
    }
}
