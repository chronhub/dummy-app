<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

use function sprintf;

final readonly class WhenCartItemAdded
{
    public function __construct(private CacheCart $cacheCart)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CartItemAdded::class,
        priority: 1
    )]
    public function updateCartCache(CartItemAdded $event): void
    {
        logger(sprintf(
            'Cart item added to cart %s for customer %s',
            $event->cartId()->toString(),
            $event->cartOwner()->toString())
        );
    }
}
