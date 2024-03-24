<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartItemRemoved
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartItemRemoved::class,
    )]
    public function updateCartCache(CartItemRemoved $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
