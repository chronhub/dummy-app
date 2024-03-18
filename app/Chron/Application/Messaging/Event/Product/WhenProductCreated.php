<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Product;

use App\Chron\Application\Service\InventoryApplicationService;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use App\Chron\Projection\ReadModel\ProductReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenProductCreated
{
    public function __construct(
        private ProductReadModel $productReadModel,
        private CatalogReadModel $catalogReadModel,
        private InventoryApplicationService $inventoryService
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: ProductCreated::class,
        priority: 0
    )]
    public function storeNewProduct(ProductCreated $event): void
    {
        $sku = $event->sku();

        $this->productReadModel->insert(
            $event->aggregateId()->toString(),
            $event->skuCode(),
            $sku->productInfo->toArray(),
            $event->productStatus()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: ProductCreated::class,
        priority: 1
    )]
    public function storeToCatalog(ProductCreated $event): void
    {
        $sku = $event->sku();

        $this->catalogReadModel->insert(
            $event->aggregateId()->toString(),
            $event->skuCode(),
            $sku->productInfo->toArray(),
            $event->productStatus()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: ProductCreated::class,
        priority: 2
    )]
    public function reportNewProductToInventory(ProductCreated $event): void
    {
        $this->inventoryService->feedInventory($event->aggregateId()->toString());
    }
}
