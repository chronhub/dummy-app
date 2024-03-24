<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenInventoryItemRefilled
{
    // todo catalog the sku as in-stock and read model
    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: InventoryItemRefilled::class,
    )]
    public function noOp(InventoryItemRefilled $event): void
    {
    }
}
