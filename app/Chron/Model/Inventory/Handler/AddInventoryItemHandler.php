<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Model\Inventory\Exception\InventoryItemAlreadyExists;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Repository\InventoryList;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.async.default',
    handles: AddInventoryItem::class,
)]
final readonly class AddInventoryItemHandler
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    public function __invoke(AddInventoryItem $command): void
    {
        $skuId = $command->skuId();

        if ($this->inventoryList->get($skuId)) {
            throw InventoryItemAlreadyExists::withId($skuId);
        }

        $inventory = Inventory::add($skuId, $command->quantity(), $command->unitPrice());

        $this->inventoryList->save($inventory);
    }
}
