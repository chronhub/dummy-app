<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemPartiallyReserved
{
    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: InventoryItemPartiallyReserved::class,
        priority: 0
    )]
    public function noOp(InventoryItemPartiallyReserved $event): void
    {
    }
}
