<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemRefilled::class,
)]
final readonly class WhenInventoryItemQuantityIncreased
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemRefilled $event): void
    {
        $this->readModel->updateQuantity(
            $event->aggregateId()->toString(),
            $event->newStock()->value,
        );
    }
}
