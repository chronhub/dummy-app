<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Cart\CartStatus;
use App\Chron\Projection\ReadModel\CartItemReadModel;
use App\Chron\Projection\ReadModel\CartReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass;

/**
 * @template TCart of object{
 *     id: string, customer_id: string, status: string,
 *     balance: string, quantity: int,
 *     closed: int, closed_reason: null|string,
 *     created_at: string, updated_at: string,
 *     items: Collection<TCartItem>
 *  }
 * @template TCartItem of object{
 *     id: string, cart_id: string, customer_id: string, sku_id: string,
 *     quantity: int, price: string,
 *     created_at: string, updated_at: string
 * }
 */
final readonly class CartProvider
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return stdClass{TCart}|null
     */
    public function findCartById(string $cartId): ?stdClass
    {
        $cart = $this->queryCart()->find($cartId);

        return $this->withCartItems($cart);
    }

    /**
     * @return stdClass{TCart}|null
     */
    public function findCartWithOwner(string $cartId, string $customerId): ?stdClass
    {
        $cart = $this->queryCart()
            ->where('id', $cartId)
            ->where('customer_id', $customerId)
            ->first();

        return $this->withCartItems($cart);
    }

    /**
     * @return stdClass{TCart}|null
     */
    public function findCartByCustomerId(string $customerId): ?stdClass
    {
        $cart = $this->queryCart()->where('customer_id', $customerId)->first();

        return $this->withCartItems($cart);
    }

    /**
     * @return stdClass{TCart}|null
     */
    public function findOpenedCartByCustomerId(string $customerId): ?stdClass
    {
        $cart = $this->queryCart()
            ->where('customer_id', $customerId)
            ->whereIn('status', [CartStatus::OPENED->value, CartStatus::SUBMITTED->value])
            ->first();

        return $this->withCartItems($cart);
    }

    /**
     * @return stdClass{TCart}|null
     */
    public function findRandomOpenedCart(): ?stdClass
    {
        $cart = $this->queryCart()
            ->where('status', CartStatus::OPENED->value)
            ->inRandomOrder()
            ->first();

        return $this->withCartItems($cart);
    }

    /**
     * @return LazyCollection{TCart}
     */
    public function findAllNonEmptyOpenedCarts(): LazyCollection
    {
        return $this->queryCart()
            ->where('status', CartStatus::OPENED->value)
            ->where('quantity', '>', 0)
            ->cursor();
    }

    /**
     * @return LazyCollection{TCart}
     */
    public function findAllSubmittedCart(): LazyCollection
    {
        return $this->queryCart()
            ->where('status', CartStatus::SUBMITTED->value)
            ->cursor();
    }

    /**
     * @return stdClass{TCart}|null
     */
    private function withCartItems(?stdClass $cart): ?stdClass
    {
        if ($cart === null) {
            return null;
        }

        $cart->items = $this->queryCartItem()->where('cart_id', $cart->id)->get();

        return $cart;
    }

    private function queryCart(): Builder
    {
        return $this->connection->table(CartReadModel::TABLE_CART);
    }

    private function queryCartItem(): Builder
    {
        return $this->connection->table(CartItemReadModel::TABLE_CART_ITEM);
    }
}
