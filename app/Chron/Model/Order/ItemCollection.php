<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use Illuminate\Support\Collection;
use RuntimeException;

final class ItemCollection
{
    public Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function put(OrderItem $orderItem)
    {
        $orderItemId = $orderItem->orderItemId->toString();

        if ($this->items->has($orderItemId)) {
            throw new RuntimeException('Order item already exists');
        }

        $this->items->put($orderItemId, $orderItem);
    }

    public function remove(OrderItem $orderItem)
    {
        $orderItemId = $orderItem->orderItemId->toString();

        if (! $this->items->has($orderItemId)) {
            throw new RuntimeException('Order item does not exist');
        }

        $this->items->forget($orderItemId);
    }

    public function has(OrderItem $orderItem): bool
    {
        $orderItemId = $orderItem->orderItemId->toString();

        return $this->items->has($orderItemId);
    }

    public function calculateBalance(): Balance
    {
        return $this->items->reduce(function (Balance $carry, OrderItem $item) {
            $price = (float) $item->unitPrice->value;
            $amount = Amount::fromString((string) ($price * $item->quantity->value));

            $carry->add($amount);

            return $carry;
        }, Balance::newInstance());
    }

    public function calculateQuantity(): Quantity
    {
        $quantity = $this->items->sum(fn (OrderItem $item) => $item->quantity->value);

        return Quantity::create($quantity);
    }
}
