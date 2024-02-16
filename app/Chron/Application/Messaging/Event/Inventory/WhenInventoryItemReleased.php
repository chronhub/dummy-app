<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemReleased::class,
)]
final readonly class WhenInventoryItemReleased
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemReleased $event): void
    {
        $this->readModel->decrement(
            $event->aggregateId()->toString(),
            $event->reserved()->value,
        );
    }
}
