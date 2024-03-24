<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemExhausted;
use Storm\Message\Attribute\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.sync.default',
    handles: InventoryItemExhausted::class,
)]
final readonly class WhenInventoryItemExhausted
{
    public function __invoke(InventoryItemExhausted $event): void
    {
        // depend on reserved stock === 0
        // put the sku on a list of out-of-stock
        // delete the sku from inventory
        logger('InventoryItemStockExhausted event has been handled.');
    }
}
