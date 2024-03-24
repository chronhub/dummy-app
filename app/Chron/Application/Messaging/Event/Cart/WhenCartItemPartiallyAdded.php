<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemPartiallyAdded;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartItemPartiallyAdded
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartItemPartiallyAdded::class,
    )]
    public function updateCartCache(CartItemPartiallyAdded $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
