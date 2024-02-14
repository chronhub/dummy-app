<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemReserved::class,
)]
final readonly class WhenInventoryItemReserved
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemReserved $event): void
    {
        $this->readModel->reserve(
            $event->aggregateId()->toString(),
            $event->reserved()->value,
        );
    }
}
