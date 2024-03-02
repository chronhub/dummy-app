<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\Cart\Service\ReadCartItems;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use Illuminate\Support\Collection;
use stdClass;

use function abs;

final class CartItemsManager
{
    private ?Collection $items = null;

    public function __construct(
        private readonly CartReservation $reservation,
        private readonly ReadCartItems $readCartItems
    ) {
    }

    public function addItem(CartItem $cartItem): ?CartItem
    {
        $reserved = $this->reservation->reserveItem($cartItem->sku->toString(), $cartItem->quantity->value);

        if ($reserved === 0) {
            return null;
        }

        $adjust = $cartItem->withAdjustedQuantity(CartItemQuantity::fromInteger($reserved));

        $this->upsertCartItem($adjust);

        return $adjust;
    }

    public function adjustItem(CartItem $cartItem): CartItem
    {
        $currentCartItem = $this->getCartItemFromSku($cartItem->sku);
        $quantityDifference = $currentCartItem->quantity->value - $cartItem->quantity->value;

        // assume increase quantity
        if ($quantityDifference === 0) {
            $quantityDifference = -1;
        }

        if ($quantityDifference < 0) {
            return $this->increaseQuantity($currentCartItem, $quantityDifference);
        }

        return $this->decreaseQuantity($currentCartItem, $quantityDifference);
    }

    public function removeItem(CartItemSku $sku, CartItemQuantity $quantity, string $reason): CartItem
    {
        $this->reservation->releaseItem($sku->toString(), $quantity->value, $reason);

        $cartItem = $this->getCartItemFromSku($sku);

        $this->items = $this->items->reject(fn (CartItem $item) => $item->sku->equalsTo($sku));

        return $cartItem;
    }

    public function calculateBalance(): CartBalance
    {
        return $this->items->reduce(
            fn (CartBalance $carry, CartItem $item) => $carry->add(
                $item->price->value,
                $item->quantity->value
            ),
            CartBalance::fromDefault()
        );
    }

    /**
     * @throws CartNotFound when cart is not found
     */
    public function load(CartId $cartId, CartOwner $ownerId): void
    {
        if ($this->items !== null) {
            return;
        }

        $items = $this->readCartItems->get($cartId->toString(), $ownerId->toString());

        if ($items === null) {
            throw CartNotFound::withCartId($cartId);
        }

        $this->items = $items->map(function (stdClass $item): CartItem {
            return CartItem::fromStrings(
                $item->id,
                $item->sku_id,
                $item->quantity,
                $item->price,
            );
        });
    }

    public function calculateQuantity(): CartQuantity
    {
        $total = $this->items->sum(fn (CartItem $item) => $item->quantity->value);

        return CartQuantity::fromInteger($total);
    }

    public function hasSku(CartItemSku $sku): bool
    {
        return $this->items->contains(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        );
    }

    public function hasCartItem(CartItemId $itemId, CartItemSku $sku): bool
    {
        return $this->items->contains(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku) && $cartItem->id->equalsTo($itemId)
        );
    }

    public function getCartItemFromSku(CartItemSku $sku): ?CartItem
    {
        return $this->items->first(
            fn (CartItem $cartItem) => $cartItem->sku->equalsTo($sku)
        );
    }

    private function upsertCartItem(CartItem $cartItem): void
    {
        $this->items = $this->items
            ->when(
                $this->hasSku($cartItem->sku),
                fn (Collection $items) => $items->map(
                    fn (CartItem $item) => $item->sku->equalsTo($cartItem->sku) ? $cartItem : $item
                ),
                fn (Collection $items) => $items->push($cartItem)
            );
    }

    /**
     * @param negative-int $quantity
     */
    private function increaseQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $reserved = $this->reservation->reserveItem($cartItem->sku->toString(), abs($quantity));

        $adjust = $this->adjustCartItem($cartItem, $reserved);

        $this->upsertCartItem($adjust);

        return $adjust;
    }

    /**
     * @param positive-int $quantity
     */
    private function decreaseQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        // todo handle case when release failed to release the reserved item quantity.
        $this->reservation->releaseItem($cartItem->sku->toString(), $quantity, InventoryReleaseReason::RESERVATION_ADJUSTED);

        $adjust = $this->adjustCartItem($cartItem, -$quantity);

        $this->upsertCartItem($adjust);

        return $adjust;
    }

    /**
     * @param positive-int|negative-int $quantity
     */
    private function adjustCartItem(CartItem $cartItem, int $quantity): CartItem
    {
        $adjustQuantity = $cartItem->quantity->value + $quantity;

        $newQuantity = CartItemQuantity::fromInteger($adjustQuantity);

        return $cartItem->withAdjustedQuantity($newQuantity);
    }
}
