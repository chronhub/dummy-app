<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemAdjusted
{
    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemAdjusted::class,
        priority: 0
    )]
    public function noOp(InventoryItemAdjusted $event): void
    {
    }
}
