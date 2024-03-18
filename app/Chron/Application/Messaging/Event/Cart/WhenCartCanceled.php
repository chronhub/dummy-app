<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartCanceled;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartCanceled
{
    public function __construct(
        private CartReadModel $cartReadModel,
        private CacheCart $cacheCart
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartCanceled::class,
        priority: 0
    )]
    public function deleteCartItems(CartCanceled $event): void
    {
        $this->cartReadModel->deleteItems(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartCanceled::class,
        priority: 1
    )]
    public function updateCart(CartCanceled $event): void
    {
        $this->cartReadModel->updateCart(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->newCartBalance()->value,
            $event->newCartQuantity()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartCanceled::class,
        priority: 2
    )]
    public function updateCartCache(CartCanceled $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
