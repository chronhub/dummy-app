<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\InventoryReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: InventoryItemPartiallyReserved::class,
)]
final readonly class WhenInventoryItemPartiallyReserved
{
    public function __construct(private InventoryReadModel $readModel)
    {
    }

    public function __invoke(InventoryItemPartiallyReserved $event): void
    {
        $this->readModel->increment(
            $event->aggregateId()->toString(),
            $event->reserved()->value,
        );
    }
}
