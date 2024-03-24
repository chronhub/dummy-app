<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class OrderItemModel
{
    private function __construct(
        public string $id,
        public string $orderId,
        public string $skuId,
        public string $price,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(object $orderItem): self
    {
        return new self(
            $orderItem->id,
            $orderItem->order_id,
            $orderItem->sku_id,
            $orderItem->unit_price,
            $orderItem->quantity,
            $orderItem->created_at,
            $orderItem->updated_at
        );
    }
}
