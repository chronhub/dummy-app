<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartSubmitted;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartCheckout
{
    public function __construct(
        private CartReadModel $cartReadModel,
        private CacheCart $cacheCart
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartSubmitted::class,
        priority: 0
    )]
    public function updateCartStatus(CartSubmitted $event): void
    {
        $this->cartReadModel->updateCartStatus(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartStatus()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartSubmitted::class,
        priority: 1
    )]
    public function updateCartCache(CartSubmitted $event): void
    {
        $this->cacheCart->update($event->cartId()->toString());
    }
}
