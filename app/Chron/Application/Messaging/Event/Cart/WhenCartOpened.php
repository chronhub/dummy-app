<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartOpened;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CartReadModel;

final readonly class WhenCartOpened
{
    public function __construct(private CartReadModel $cartReadModel)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartOpened::class,
    )]
    public function createNewCart(CartOpened $event): void
    {
        $this->cartReadModel->insert(
            $event->aggregateId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartStatus()->value
        );
    }
}
