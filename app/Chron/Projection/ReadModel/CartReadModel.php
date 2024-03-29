<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class CartReadModel
{
    final public const string TABLE_CART = 'read_cart';

    final public const string TABLE_CART_ITEM = 'read_cart_item';

    public function __construct(private Connection $connection)
    {
    }

    public function insert(string $cartId, string $customerId, string $status): void
    {
        $this->queryCart()->insert([
            'id' => $cartId,
            'customer_id' => $customerId,
            'status' => $status,
        ]);
    }

    public function updateCart(string $cartId, string $customerId, string $balance, int $quantity): void
    {
        $this->queryCart()
            ->where('id', $cartId)
            ->where('customer_id', $customerId)
            ->update(['balance' => $balance, 'quantity' => $quantity]);
    }

    public function updateCartStatus(string $cartId, string $customerId, string $status): void
    {
        $this->queryCart()
            ->where('id', $cartId)
            ->where('customer_id', $customerId)
            ->update(['status' => $status]);
    }

    public function insertCartItem(
        string $cartItemId,
        string $cartId,
        string $customerId,
        string $skuId,
        string $unitPrice,
        int $quantity,
    ): void {
        $this->queryCartItem()->insert([
            'id' => $cartItemId,
            'cart_id' => $cartId,
            'customer_id' => $customerId,
            'sku_id' => $skuId,
            'quantity' => $quantity,
            'price' => $unitPrice,
        ]);
    }

    public function deleteItem(string $cartItemId, string $cartId, string $customerId, string $skuId): void
    {
        $this->queryCartItem()
            ->where('id', $cartItemId)
            ->where('cart_id', $cartId)
            ->where('customer_id', $customerId)
            ->where('sku_id', $skuId)
            ->delete();
    }

    public function deleteItems(string $cartId, string $customerId): void
    {
        $this->queryCartItem()
            ->where('cart_id', $cartId)
            ->where('customer_id', $customerId)
            ->delete();
    }

    public function updateItemQuantity(string $cartItemId, string $cartId, string $customerId, string $skuId, int $quantity): void
    {
        $this->queryCartItem()
            ->where('id', $cartItemId)
            ->where('cart_id', $cartId)
            ->where('customer_id', $customerId)
            ->where('sku_id', $skuId)
            ->update(['quantity' => $quantity]);
    }

    public function deleteCart(string $cartOwner): void
    {
        $cart = $this->queryCart()
            ->where('customer_id', $cartOwner)
            ->where('status', 'submitted')
            ->first();

        $this->queryCart()->delete($cart->id);

        $this->queryCartItem()
            ->where('cart_id', $cart->id)
            ->delete();
    }

    private function queryCart(): Builder
    {
        return $this->connection->table(self::TABLE_CART);
    }

    private function queryCartItem(): Builder
    {
        return $this->connection->table(self::TABLE_CART_ITEM);
    }
}
