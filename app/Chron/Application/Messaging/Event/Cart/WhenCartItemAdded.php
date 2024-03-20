<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartItemAdded
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemAdded::class,
        priority: 1
    )]
    public function updateCartCache(CartItemAdded $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
