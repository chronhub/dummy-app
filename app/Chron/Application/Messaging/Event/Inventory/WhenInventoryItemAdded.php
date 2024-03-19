<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemAdded
{
    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdded::class,
        priority: 0
    )]
    public function noOp(InventoryItemAdded $event): void
    {
    }
}
