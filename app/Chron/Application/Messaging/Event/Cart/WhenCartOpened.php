<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartOpened;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Http\Controllers\Action\Cart\CacheCart;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCartOpened
{
    public function __construct(
        private CartReadModel $cartReadModel,
        private CacheCart $cacheCart
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartOpened::class,
        priority: 0
    )]
    public function createNewCart(CartOpened $event): void
    {
        $this->cartReadModel->insert(
            $event->aggregateId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartStatus()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartOpened::class,
        priority: 1
    )]
    public function updateCartCache(CartOpened $event): void
    {
        $this->cacheCart->update($event->aggregateId()->toString());
    }
}
