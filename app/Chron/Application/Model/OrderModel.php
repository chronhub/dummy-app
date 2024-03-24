<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use Illuminate\Support\Collection;

final readonly class OrderModel
{
    private function __construct(
        public string $id,
        //public string $cartId, todo
        public string $customerId,
        public string $status,
        public string $balance,
        public string $quantity,
        public string $createdAt,
        public ?string $updatedAt,
        public ?Collection $orderItems
    ) {
    }

    public static function fromObject(object $order, ?Collection $orderItems): self
    {
        return new self(
            $order->id,
            //$order->cart_id, todo
            $order->customer_id,
            $order->status,
            $order->balance,
            $order->quantity,
            $order->created_at,
            $order->updated_at,
            $orderItems?->map(fn ($orderItem) => OrderItemModel::fromObject($orderItem))
        );
    }
}
