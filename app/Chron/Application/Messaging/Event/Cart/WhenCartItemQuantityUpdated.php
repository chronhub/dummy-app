<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartItemQuantityUpdated
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartItemQuantityUpdated::class,
    )]
    public function updateCartCache(CartItemQuantityUpdated $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
