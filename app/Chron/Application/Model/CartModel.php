<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use stdClass;

final readonly class CartModel
{
    private function __construct(
        public string $id,
        public string $customerId,
        public string $status,
        public string $balance,
        public int $quantity,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(stdClass $cart): self
    {
        return new CartModel(
            $cart->id,
            $cart->customer_id,
            $cart->status,
            $cart->balance,
            $cart->quantity,
            $cart->created_at,
            $cart->updated_at
        );
    }
}
