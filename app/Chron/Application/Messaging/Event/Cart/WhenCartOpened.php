<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartOpened;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartOpened
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartOpened::class,
    )]
    public function updateCartCache(CartOpened $event): void
    {
        $this->cacheCart->update($event->aggregateId()->toString());
    }
}
