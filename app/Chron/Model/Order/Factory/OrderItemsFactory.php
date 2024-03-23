<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Factory;

use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Order\ItemCollection;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderItemId;
use App\Chron\Model\Order\Quantity;
use Illuminate\Support\Collection;

final class OrderItemsFactory
{
    public static function make(Collection $cartItems, OrderId $orderId): ItemCollection
    {
        $items = new ItemCollection($orderId);

        $cartItems->each(function (object $cartItem) use ($items) {
            $orderItem = OrderItem::fromValues(
                OrderItemId::fromString($cartItem->id),
                SkuId::fromString($cartItem->sku_id),
                UnitPrice::create($cartItem->price),
                Quantity::create($cartItem->quantity)
            );

            $items->put($orderItem);
        });

        return $items;
    }
}
