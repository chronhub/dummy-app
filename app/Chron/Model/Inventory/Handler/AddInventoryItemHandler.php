<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Model\Inventory\Exception\InventoryItemAlreadyExists;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: AddInventoryItem::class,
)]
final readonly class AddInventoryItemHandler
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    public function __invoke(AddInventoryItem $command): void
    {
        $skuId = SkuId::fromString($command->content['sku_id']);

        if ($this->inventoryList->get($skuId)) {
            throw InventoryItemAlreadyExists::withId($skuId);
        }

        $inventory = Inventory::add(
            $skuId,
            Quantity::create($command->content['quantity']),
            UnitPrice::create($command->content['unit_price'])
        );

        $this->inventoryList->save($inventory);
    }
}
