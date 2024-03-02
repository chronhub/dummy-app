<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Model\Cart\Event\CartItemPartiallyAdded;
use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Chron\Model\Cart\Event\CartOpened;
use App\Chron\Model\Cart\Exception\CartAlreadyExists;
use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\Cart\Exception\InsufficientStockForCartItem;
use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\Exception\InvalidCartOperation;
use App\Chron\Model\Inventory\InventoryReleaseReason;
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

    public static function open(CartId $cartId, CartOwner $cartOwner): self
    {
        $cart = new self($cartId);

        $cart->recordThat(CartOpened::forOwner($cartId, $cartOwner, CartStatus::OPENED));

        return $cart;
    }

    public function addItem(CartItem $cartItem, CartItemsManager $itemsManager): void
    {
        $this->assertCartOpenForModification();

        $itemsManager->load($this->cartId(), $this->owner);

        $this->assertCartItemNotExists($cartItem, $itemsManager);

        $newCartItem = $itemsManager->addItem($cartItem);

        if ($newCartItem === null) {
            throw InsufficientStockForCartItem::withId($this->cartId(), $cartItem->sku);
        }

        if ($cartItem->sameValueAs($newCartItem)) {
            $this->recordThat(CartItemAdded::forCart(
                $this->cartId(),
                $this->owner,
                $newCartItem,
                $itemsManager->calculateBalance(),
                $itemsManager->calculateQuantity()
            ));

            return;
        }

        $this->recordThat(CartItemPartiallyAdded::forCart(
            $this->cartId(),
            $this->owner,
            $newCartItem,
            $itemsManager->calculateBalance(),
            $itemsManager->calculateQuantity(),
            $cartItem->quantity
        ));
    }

    public function removeItem(CartItemId $cartItemId, CartItemSku $cartItemSku, CartItemsManager $itemsManager): void
    {
        $this->assertCartOpenForModification();

        $itemsManager->load($this->cartId(), $this->owner);

        $this->assertCartItemExists($cartItemId, $cartItemSku, $itemsManager);

        $cartItem = $itemsManager->getCartItemFromSku($cartItemSku);

        $itemsManager->removeItem($cartItem->sku, $cartItem->quantity, InventoryReleaseReason::RESERVATION_CANCELED);

        $this->recordThat(CartItemRemoved::forCart(
            $this->cartId(),
            $this->owner,
            $cartItem,
            $itemsManager->calculateBalance(),
            $itemsManager->calculateQuantity()
        ));
    }

    public function updateItemQuantity(CartItem $cartItem, CartItemsManager $itemsManager): void
    {
        $this->assertCartOpenForModification();

        $itemsManager->load($this->cartId(), $this->owner);

        $this->assertCartItemExists($cartItem->id, $cartItem->sku, $itemsManager);

        $adjust = $itemsManager->adjustItem($cartItem);

        // todo add partial update event when increasing quantity

        $this->recordThat(CartItemQuantityUpdated::forCart(
            $this->cartId(),
            $this->owner,
            $adjust,
            $itemsManager->calculateBalance(),
            $itemsManager->calculateQuantity()
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

    private function assertCartOpenForModification(): void
    {
        if ($this->status !== CartStatus::OPENED) {
            throw new InvalidCartOperation(sprintf('Cart with id %s is not opened', $this->cartId()));
        }
    }

    private function assertCartItemExists(CartItemId $cartItemId, CartItemSku $cartItemSku, CartItemsManager $itemsManager): void
    {
        if (! $itemsManager->hasCartItem($cartItemId, $cartItemSku)) {
            throw CartNotFound::withCartItemSku($cartItemSku, $cartItemId, $this->cartId());
        }
    }

    private function assertCartItemNotExists(CartItem $cartItem, CartItemsManager $itemsManager): void
    {
        if ($itemsManager->hasSku($cartItem->sku)) {
            throw CartAlreadyExists::withCartItemId(
                $itemsManager->getCartItemFromSku($cartItem->sku)->id,
                $this->cartId()
            );
        }
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof CartOpened:
                $this->owner = $event->cartOwner();
                $this->status = $event->cartStatus();

                break;

            case $event instanceof CartItemAdded:
            case $event instanceof CartItemPartiallyAdded:
            case $event instanceof CartItemRemoved:
            case $event instanceof CartItemQuantityUpdated:
                break;

            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
