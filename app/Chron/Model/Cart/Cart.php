<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Chron\Model\Cart\Event\CartOpened;
use App\Chron\Model\Cart\Exception\CartAlreadyExists;
use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\Exception\InvalidCartOperation;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

use function sprintf;

final class Cart implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CartOwner $owner;

    private CartStatus $status;

    private CartItems $items;

    public static function open(CartId $cartId, CartOwner $cartOwner): self
    {
        $cart = new self($cartId);

        $cart->recordThat(CartOpened::forOwner($cartId, $cartOwner, CartStatus::OPENED));

        return $cart;
    }

    public function addItem(CartItem $cartItem): void
    {
        $this->assertCartOpenForModification();

        if ($this->items->hasSku($cartItem->sku)) {
            throw CartAlreadyExists::withCartItemId($this->items->getCartItemIdFromSku($cartItem->sku), $this->cartId());
        }

        // todo move collection to entity and add balance and quantity as properties
        $items = $this->items;
        $items->add($cartItem);

        $this->recordThat(CartItemAdded::forCart(
            $this->cartId(),
            $this->owner,
            $cartItem,
            $items->calculateBalance(),
            $items->calculateQuantity()
        ));
    }

    public function removeItem(CartItemSku $cartItemSku): void
    {
        $this->assertCartOpenForModification();
        $this->assertCartItemSkuExists($cartItemSku);

        $cartItem = $this->items->getCartItemBySku($cartItemSku);

        $items = $this->items;
        $items->remove($cartItemSku);

        $this->recordThat(CartItemRemoved::forCart(
            $this->cartId(),
            $this->owner,
            $cartItem,
            $items->calculateBalance(),
            $items->calculateQuantity()
        ));
    }

    public function updateItemQuantity(CartItem $cartItem): void
    {
        $this->assertCartOpenForModification();

        if (! $this->items->hasCartItem($cartItem->id, $cartItem->sku)) {
            throw CartNotFound::withCartItemSku($cartItem->sku, $cartItem->id, $this->cartId());
        }

        $items = $this->items;
        $items->remove($cartItem->sku);
        $items->add($cartItem);

        $this->recordThat(CartItemQuantityUpdated::forCart(
            $this->cartId(),
            $this->owner,
            $cartItem,
            $items->calculateBalance(),
            $items->calculateQuantity()
        ));
    }

    public function checkout(): void
    {

    }

    public function close(string $reason): void
    {

    }

    public function cartId(): CartId
    {
        /** @var AggregateIdentity&CartId $cartId */
        $cartId = $this->identity;

        return $cartId;
    }

    public function owner(): CartOwner
    {
        return $this->owner;
    }

    public function status(): CartStatus
    {
        return $this->status;
    }

    public function items(): CartItems
    {
        return clone $this->items;
    }

    private function assertCartOpenForModification(): void
    {
        if ($this->status !== CartStatus::OPENED) {
            throw new InvalidCartOperation(sprintf('Cart with id %s is not opened', $this->cartId()));
        }
    }

    private function assertCartItemSkuExists(CartItemSku $sku): void
    {
        if (! $this->items->hasSku($sku)) {
            throw CartNotFound::withCartItemSku($sku, $this->items->getCartItemIdFromSku($sku), $this->cartId());
        }
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof CartOpened:
                $this->owner = $event->cartOwner();
                $this->status = $event->cartStatus();
                $this->items = CartItems::create();

                break;

            case $event instanceof CartItemAdded:
                $this->items->add($event->cartItem());

                break;

            case $event instanceof CartItemRemoved:
                $this->items->remove($event->oldCartItem()->sku);

                break;

            case $event instanceof CartItemQuantityUpdated:
                $this->items->remove($event->cartItem()->sku);
                $this->items->add($event->cartItem());

                break;

            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
