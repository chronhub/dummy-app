<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Product;

use App\Chron\Application\Service\InventoryApplicationService;
use App\Chron\Model\Product\Event\ProductCreated;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenProductCreated
{
    public function __construct(private InventoryApplicationService $inventoryService)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: ProductCreated::class,
        priority: 1
    )]
    public function reportNewProductToInventory(ProductCreated $event): void
    {
        $this->inventoryService->feedInventory($event->aggregateId()->toString());
    }
}
