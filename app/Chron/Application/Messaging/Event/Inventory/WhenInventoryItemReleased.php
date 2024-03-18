<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemReleased
{
    public function __construct(
        private InventoryReadModel $inventoryReadModel,
        private CatalogReadModel $catalogReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemReleased::class,
        priority: 0
    )]
    public function releaseInventoryProduct(InventoryItemReleased $event): void
    {
        $this->inventoryReadModel->decrement(
            $event->aggregateId()->toString(),
            $event->released()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemReleased::class,
        priority: 1
    )]
    public function updateCatalogProductReservation(InventoryItemReleased $event): void
    {
        $this->catalogReadModel->updateReservation(
            $event->aggregateId()->toString(),
            $event->totalReserved()->value,
        );
    }
}
