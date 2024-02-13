<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemQuantityIncreased;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemQuantityIncreased::class,
)]
final readonly class WhenInventoryItemQuantityIncreased
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemQuantityIncreased $event): void
    {
        $this->readModel->updateQuantity(
            $event->aggregateId()->toString(),
            $event->newStock()->value,
        );
    }
}
