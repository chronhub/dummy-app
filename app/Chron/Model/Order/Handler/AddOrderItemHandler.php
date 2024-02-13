<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Model\Order\Service\OrderReservationService;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: AddOrderItem::class,
)]
final readonly class AddOrderItemHandler
{
    public function __construct(
        private OrderList $orders,
        private OrderReservationService $reservationService,
    ) {
    }

    public function __invoke(AddOrderItem $command): void
    {
        $orderId = OrderId::fromString($command->content['order_id']);

        $order = $this->orders->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withId($orderId);
        }

        $order->addOrderItem(OrderItem::fromArray($command->toContent()), $this->reservationService);

        $this->orders->save($order);
    }
}
