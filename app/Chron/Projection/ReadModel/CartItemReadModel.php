<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Model\Cart\Event\CartItemPartiallyAdded;
use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use Illuminate\Database\Schema\Blueprint;

final class CartItemReadModel extends ReadModelConnection
{
    final public const string TABLE_CART_ITEM = 'read_cart_item';

    protected function insert(CartItemAdded|CartItemPartiallyAdded $event): void
    {
        $cartItem = $event->cartItem();

        $this->query()->insert([
            'id' => $cartItem->id->toString(),
            'cart_id' => $event->cartId()->toString(),
            'customer_id' => $event->cartOwner()->toString(),
            'sku_id' => $cartItem->sku->toString(),
            'quantity' => $cartItem->quantity->value,
            'price' => $cartItem->price->value,
        ]);
    }

    protected function deleteOne(string $cartItemId, string $cartId, string $customerId, string $skuId): void
    {
        $this->query()
            ->where('id', $cartItemId)
            ->where('cart_id', $cartId)
            ->where('customer_id', $customerId)
            ->where('sku_id', $skuId)
            ->delete();
    }

    protected function deleteAll(string $cartId, string $customerId): void
    {
        $this->query()
            ->where('cart_id', $cartId)
            ->where('customer_id', $customerId)
            ->delete();
    }

    protected function deleteSubmitted(string $customerId): void
    {
        $this->query()->where('customer_id', $customerId)->delete();
    }

    protected function updateQuantity(CartItemQuantityUpdated $event): void
    {
        $item = $event->cartItem();

        $this->query()
            ->where('id', $item->id->toString())
            ->where('cart_id', $event->cartId()->toString())
            ->where('customer_id', $event->cartOwner()->toString())
            ->where('sku_id', $item->sku->toString())
            ->update(['quantity' => $item->quantity->value]);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('cart_id');
            $table->uuid('customer_id');
            $table->uuid('sku_id');
            $table->unsignedInteger('quantity');
            $table->string('price');

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        };
    }

    protected function tableName(): string
    {
        return self::TABLE_CART_ITEM;
    }
}
