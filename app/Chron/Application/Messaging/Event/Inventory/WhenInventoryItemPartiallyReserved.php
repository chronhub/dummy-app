<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemPartiallyReserved
{
    public function __construct(
        private InventoryReadModel $inventoryReadModel,
        private CatalogReadModel $catalogReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemPartiallyReserved::class,
        priority: 0
    )]
    public function incrementInventoryProductReservation(InventoryItemPartiallyReserved $event): void
    {
        $this->inventoryReadModel->increment(
            $event->aggregateId()->toString(),
            $event->reserved()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemPartiallyReserved::class,
        priority: 1
    )]
    public function updateCatalogProductReservation(InventoryItemPartiallyReserved $event): void
    {
        $this->catalogReadModel->updateReservation(
            $event->aggregateId()->toString(),
            $event->totalReserved()->value,
        );
    }
}
