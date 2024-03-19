<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemReleased
{
    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: InventoryItemReleased::class,
        priority: 0
    )]
    public function noOp(InventoryItemReleased $event): void
    {
    }
}
