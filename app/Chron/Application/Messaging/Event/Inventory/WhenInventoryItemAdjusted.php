<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemAdjusted::class,
)]
final readonly class WhenInventoryItemAdjusted
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemAdjusted $event): void
    {
        $this->readModel->updateQuantity(
            $event->aggregateId()->toString(),
            $event->totalStock()->value,
        );
    }
}
