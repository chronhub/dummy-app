<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use Illuminate\Support\Collection;

final readonly class CartItemModel
{
    private function __construct(
        public string $id,
        public string $cartId,
        public string $customerId,
        public string $sku,
        public string $price,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt,
        public ?Collection $cartItems
    ) {
    }

    public static function fromObject(object $cartItem, ?Collection $cartItems): self
    {
        return new CartItemModel(
            $cartItem->id,
            $cartItem->cart_id,
            $cartItem->customer_id,
            $cartItem->sku,
            $cartItem->price,
            $cartItem->quantity,
            $cartItem->created_at,
            $cartItem->updated_at,
            $cartItems?->map(fn ($cartItem) => CartItemModel::fromObject($cartItem, null))
        );
    }
}
