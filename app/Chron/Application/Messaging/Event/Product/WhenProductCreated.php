<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Product;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\ReadModel\ProductReadModel;
use Symfony\Component\Uid\Uuid;

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
    public function toReadModel(ProductCreated $event): void
    {
        $this->readModel->insert(
            $event->aggregateId()->toString(),
            $event->productInfo()->toArray(),
            $event->productStatus()->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: ProductCreated::class,
        priority: 1
    )]
    public function reportToInventory(ProductCreated $event): void
    {
        Report::relay(
            AddInventoryItem::withItem(
                Uuid::v4()->jsonSerialize(),
                $event->aggregateId()->toString(),
                $event->productInfo()->toArray(),
                fake()->numberBetween(1000, 10000),
                (string) fake()->randomFloat(2, 10, 4000),
            )
        );
    }
}
