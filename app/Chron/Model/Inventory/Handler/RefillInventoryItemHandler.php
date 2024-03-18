<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Inventory\RefillInventoryItem;
use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Repository\InventoryList;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: RefillInventoryItem::class,
)]
final readonly class RefillInventoryItemHandler
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    public function __invoke(RefillInventoryItem $command): void
    {
        $skuId = $command->skuId();

        $inventory = $this->inventoryList->get($skuId);

        if (! $inventory instanceof Inventory) {
            throw InventoryItemNotFound::withId($skuId);
        }

        $inventory->refill($command->quantity());

        $this->inventoryList->save($inventory);
    }
}
