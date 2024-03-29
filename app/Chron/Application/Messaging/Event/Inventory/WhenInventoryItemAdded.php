<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemAdded
{
    public function __construct(
        private InventoryReadModel $inventoryReadModel,
        private CatalogReadModel $catalogReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdded::class,
        priority: 0
    )]
    public function storeInventoryProduct(InventoryItemAdded $event): void
    {
        $this->inventoryReadModel->insert(
            $event->aggregateId()->toString(),
            $event->totalStock()->value,
            $event->unitPrice()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdded::class,
        priority: 1
    )]
    public function storeCatalogProduct(InventoryItemAdded $event): void
    {
        $this->catalogReadModel->updateProductQuantityAndPrice(
            $event->aggregateId()->toString(),
            $event->totalStock()->value,
            $event->unitPrice()->value,
        );
    }
}
