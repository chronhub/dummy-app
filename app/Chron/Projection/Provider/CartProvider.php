<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Cart\CartStatus;
use App\Chron\Projection\ReadModel\CartReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class CartProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findCartById(string $cartId): ?stdClass
    {
        return $this->queryCart()->find($cartId);
    }

    public function findCartByCustomerId(string $customerId): ?stdClass
    {
        return $this->queryCart()->where('customer_id', $customerId)->first();
    }

    public function findOpenedCartByCustomerId(string $customerId): ?stdClass
    {
        return $this->queryCart()
            ->where('customer_id', $customerId)
            ->where('status', CartStatus::OPENED->value)
            ->first();
    }

    public function findRandomOpenedCart(): ?stdClass
    {
        $cart = $this->queryCart()
            ->where('status', CartStatus::OPENED->value)
            ->inRandomOrder()
            ->first();

        if ($cart === null) {
            return null;
        }

        $cart->items = $this->queryCartItem()
            ->where('cart_id', $cart->id)
            ->get();

        return $cart;
    }

    private function queryCart(): Builder
    {
        return $this->connection->table(CartReadModel::TABLE_CART);
    }

    private function queryCartItem(): Builder
    {
        return $this->connection->table(CartReadModel::TABLE_CART_ITEM);
    }
}
