<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemAdjusted
{
    public function __construct(
        private InventoryReadModel $inventoryReadModel,
        private CatalogReadModel $catalogReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdjusted::class,
        priority: 0
    )]
    public function updateInventoryProductQuantity(InventoryItemAdjusted $event): void
    {
        $this->inventoryReadModel->updateQuantity(
            $event->aggregateId()->toString(),
            $event->totalStock()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdjusted::class,
        priority: 1
    )]
    public function updateCatalogProductReservation(InventoryItemAdjusted $event): void
    {
        $this->catalogReadModel->updateReservation(
            $event->aggregateId()->toString(),
            $event->totalReserved()->value,
        );
    }
}
