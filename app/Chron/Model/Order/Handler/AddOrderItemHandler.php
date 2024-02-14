<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: AddOrderItem::class,
)]
final readonly class AddOrderItemHandler
{
    public function __construct(
        private OrderList $orders,
        private InventoryList $inventoryList,
    ) {
    }

    public function __invoke(AddOrderItem $command): void
    {
        $orderId = OrderId::fromString($command->content['order_id']);

        $order = $this->orders->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withId($orderId);
        }

        $skuId = SkuId::fromString($command->content['sku_id']);
        $inventory = $this->inventoryList->get($skuId);

        if ($inventory === null) {
            throw InventoryItemNotFound::withId($skuId);
        }

        $order->addOrderItem(OrderItem::fromArray($command->toContent()), $inventory);

        $this->inventoryList->save($inventory);

        $this->orders->save($order);
    }
}
