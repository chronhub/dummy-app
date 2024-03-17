<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\InventoryReadModel;

final readonly class WhenInventoryItemReserved
{
    public function __construct(
        private InventoryReadModel $inventoryReadModel,
        private CatalogReadModel $catalogReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemReserved::class,
        priority: 0
    )]
    public function reserveInventoryProduct(InventoryItemReserved $event): void
    {
        $this->inventoryReadModel->increment(
            $event->aggregateId()->toString(),
            $event->reserved()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemReserved::class,
        priority: 1
    )]
    public function updateCatalogReservation(InventoryItemReserved $event): void
    {
        $this->catalogReadModel->updateReservation(
            $event->aggregateId()->toString(),
            $event->totalReserved()->value,
        );
    }
}
