<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Product;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\ReadModel\ProductReadModel;

final readonly class WhenProductCreated
{
    public function __construct(private ProductReadModel $readModel)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: ProductCreated::class,
        priority: 0
    )]
    public function storeNewProduct(ProductCreated $event): void
    {
        $sku = $event->sku();

        $this->readModel->insert(
            $sku->productId->toString(),
            $sku->skuId->toString(),
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
    public function reportNewProductToInventory(ProductCreated $event): void
    {
        Report::relay(
            AddInventoryItem::withItem(
                $event->skuId()->toString(),
                $event->aggregateId()->toString(),
                fake()->numberBetween(1000, 10000),
                (string) fake()->randomFloat(2, 10, 4000),
            )
        );
    }
}
