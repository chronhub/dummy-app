<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartItemQuantityUpdated
{
    public function __construct(
        private CartReadModel $cartReadModel,
        private CacheCart $cacheCart
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemQuantityUpdated::class,
        priority: 0
    )]
    public function insertCartItem(CartItemQuantityUpdated $event): void
    {
        $cartItem = $event->cartItem();

        $this->cartReadModel->updateItemQuantity(
            $cartItem->id->toString(),
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $cartItem->sku->toString(),
            $cartItem->quantity->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemQuantityUpdated::class,
        priority: 1
    )]
    public function updateCart(CartItemQuantityUpdated $event)
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
        handles: CartItemQuantityUpdated::class,
        priority: 2
    )]
    public function updateCartCache(CartItemQuantityUpdated $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
