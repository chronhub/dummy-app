<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class OrderItemModel
{
    private function __construct(
        public string $id,
        public string $orderId,
        public string $sku,
        public string $price,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(object $orderItem): self
    {
        return new OrderItemModel(
            $orderItem->id,
            $orderItem->order_id,
            $orderItem->sku,
            $orderItem->unit_price,
            $orderItem->quantity,
            $orderItem->created_at,
            $orderItem->updated_at
        );
    }
}
