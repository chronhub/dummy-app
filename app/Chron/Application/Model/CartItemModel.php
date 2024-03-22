<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class CartItemModel
{
    private function __construct(
        public string $id,
        public string $cartId,
        public string $customerId,
        public string $skuId,
        public string $price,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    public static function fromObject(object $cartItem): self
    {
        return new CartItemModel(
            $cartItem->id,
            $cartItem->cart_id,
            $cartItem->customer_id,
            $cartItem->sku_id,
            $cartItem->price,
            $cartItem->quantity,
            $cartItem->created_at,
            $cartItem->updated_at,
        );
    }
}
