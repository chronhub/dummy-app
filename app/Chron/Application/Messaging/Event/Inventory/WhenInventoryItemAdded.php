<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemAdded::class,
)]
final readonly class WhenInventoryItemAdded
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemAdded $event): void
    {
        $this->readModel->insert(
            $event->aggregateId()->toString(),
            $event->totalStock()->value,
            $event->unitPrice()->value,
        );
    }
}
