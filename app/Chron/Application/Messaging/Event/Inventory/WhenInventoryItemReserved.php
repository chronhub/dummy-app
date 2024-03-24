<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemReserved
{
    public function __construct()
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: InventoryItemReserved::class,
        priority: 0
    )]
    public function nOp(InventoryItemReserved $event): void
    {
    }
}
