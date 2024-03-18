<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Order\Exception\OrderAlreadyExists;
use App\Chron\Model\Order\Exception\OrderNotFound;
use Illuminate\Support\Collection;

final readonly class ItemCollection
{
    /**
     * @var Collection<OrderItem>
     */
    public Collection $items;

    public OrderId $orderId;

    public function __construct(OrderId $orderId)
    {
        $this->orderId = $orderId;
        $this->items = new Collection();
    }

    public function put(OrderItem $orderItem): void
    {
        $orderItemId = $orderItem->orderItemId->toString();

        if ($this->items->has($orderItemId)) {
            throw OrderAlreadyExists::withOrderItemId($this->orderId, $orderItem->orderItemId);
        }

        $this->items->put($orderItemId, $orderItem);
    }

    public function remove(OrderItem $orderItem): void
    {
        $orderItemId = $orderItem->orderItemId->toString();

        if (! $this->items->has($orderItemId)) {
            throw OrderNotFound::withOrderItemId($this->orderId, $orderItem->orderItemId);
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

    public static function fromArray(OrderId $orderId, array $items): self
    {
        $collection = new self($orderId);

        foreach ($items as $item) {
            $orderItem = OrderItem::fromArray($item);

            $collection->put($orderItem);
        }

        return $collection;
    }

    /**
     * @return Collection<OrderItem>
     */
    public function getItems(): Collection
    {
        return clone $this->items;
    }

    public function toArray(): array
    {
        return $this->items->map(fn (OrderItem $item) => $item->toArray())->toArray();
    }
}
