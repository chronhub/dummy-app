<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use Illuminate\Support\Collection;

final readonly class CartModel
{
    private function __construct(
        public string $id,
        public string $customerId,
        public string $status,
        public string $balance,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt,
        /** @var Collection{CartItemModel}|null */
        public ?Collection $cartItems
    ) {
    }

    public static function fromObject(object $cart, ?Collection $cartItems): self
    {
        return new self(
            $cart->id,
            $cart->customer_id,
            $cart->status,
            $cart->balance,
            $cart->quantity,
            $cart->created_at,
            $cart->updated_at,
            $cartItems?->map(fn (object $cartItem): CartItemModel => CartItemModel::fromObject($cartItem))
        );
    }
}
