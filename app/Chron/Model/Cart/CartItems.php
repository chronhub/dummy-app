<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use Illuminate\Support\Collection;

final class CartItems
{
    private function __construct(private Collection $items)
    {
    }

    public static function create(): self
    {
        return new self(new Collection());
    }

    public function add(CartItem $item): void
    {
        $this->items->push($item);
    }

    public function remove(CartItemSku $sku): void
    {
        $this->items = $this->items->reject(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        );
    }

    public function hasSku(CartItemSku $sku): bool
    {
        return $this->items->contains(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        );
    }

    public function hasCartItem(CartItemId $cartItemId, CartItemSku $sku): bool
    {
        return $this->items->contains(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku) && $cartItem->id->equalsTo($cartItemId)
        );
    }

    public function getCartItemBySku(CartItemSku $sku): ?CartItem
    {
        return $this->items->first(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        );
    }

    public function getCartItemIdFromSku(CartItemSku $sku): ?CartItemId
    {
        return $this->items->first(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        )?->id;
    }

    public function calculateBalance(): CartBalance
    {
        return $this->items->reduce(
            fn (CartBalance $carry, CartItem $item) => $carry->add(
                $item->price->value,
                $item->quantity->value
            ),
            CartBalance::fromString('0.00')
        );
    }

    public function calculateQuantity(): CartQuantity
    {
        $total = $this->items->sum(
            fn (CartItem $item) => $item->quantity->value
        );

        return CartQuantity::fromInteger($total);
    }

    public function items(): Collection
    {
        return clone $this->items;
    }
}
