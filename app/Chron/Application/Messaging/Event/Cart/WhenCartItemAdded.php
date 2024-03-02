<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Http\Controllers\Action\Cart\CacheCart;

final readonly class WhenCartItemAdded
{
    public function __construct(
        private CartReadModel $cartReadModel,
        private CacheCart $cacheCart
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemAdded::class,
        priority: 0
    )]
    public function insertCartItem(CartItemAdded $event): void
    {
        $cartItem = $event->cartItem();

        $this->cartReadModel->insertCartItem(
            $cartItem->id->toString(),
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $cartItem->sku->toString(),
            $cartItem->price->value,
            $cartItem->quantity->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemAdded::class,
        priority: 1
    )]
    public function updateCart(CartItemAdded $event): void
    {
        $this->cartReadModel->updateCart(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartBalance()->value,
            $event->cartQuantity()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemAdded::class,
        priority: 2
    )]
    public function updateCartCache(CartItemAdded $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
