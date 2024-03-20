<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartCanceled;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartCanceled
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartCanceled::class,
    )]
    public function updateCartCache(CartCanceled $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
