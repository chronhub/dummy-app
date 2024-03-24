<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartSubmitted;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartCheckout
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartSubmitted::class,
    )]
    public function updateCartCache(CartSubmitted $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
