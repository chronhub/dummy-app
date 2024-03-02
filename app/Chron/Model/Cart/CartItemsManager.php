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

    public function addItem(CartItem $cartItem): ?CartItem
    {
        $reserved = $this->reservation->reserveItem($cartItem->sku->toString(), $cartItem->quantity->value);

        if ($reserved === 0) {
            return null;
        }

        $adjust = $this->processCartItemAddition($cartItem, $reserved);

        $this->replaceCartItem($adjust);

        return $adjust;
    }

    public function adjustItem(CartItem $cartItem): CartItem
    {
        $currentCartItem = $this->getCartItemFromSku($cartItem->sku);
        $quantityDifference = $currentCartItem->quantity->value - $cartItem->quantity->value;

        // assume increase quantity if the difference is 0
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

    private function replaceCartItem(CartItem $cartItem): void
    {
        $this->items = $this->items->map(
            fn (CartItem $item) => $item->sku->equalsTo($cartItem->sku) ? $cartItem : $item
        );
    }

    /**
     * @param negative-int $quantity
     */
    private function increaseQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $reserved = $this->reservation->reserveItem($cartItem->sku->toString(), abs($quantity));

        $adjust = $this->processCartItemAddition($cartItem, $reserved);

        $this->replaceCartItem($adjust);

        return $adjust;
    }

    /**
     * @param positive-int $quantity
     */
    private function decreaseQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $this->reservation->releaseItem($cartItem->sku->toString(), $quantity, InventoryReleaseReason::RESERVATION_ADJUSTED);

        $adjust = $cartItem->withAdjustedQuantity(CartItemQuantity::fromInteger($cartItem->quantity->value - $quantity));

        $this->replaceCartItem($adjust);

        return $adjust;
    }

    /**
     * @param positive-int $quantityReserved
     */
    private function processCartItemAddition(CartItem $cartItem, int $quantityReserved): CartItem
    {
        return $cartItem->withAdjustedQuantity(CartItemQuantity::fromInteger(
            $cartItem->quantity->value + $quantityReserved
        ));
    }
}
