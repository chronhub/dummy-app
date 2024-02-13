<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Inventory\RefillInventoryItem;
use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

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
        $skuId = SkuId::fromString($command->content['sku_id']);

        $inventory = $this->inventoryList->get($skuId);

        if (! $inventory instanceof Inventory) {
            throw InventoryItemNotFound::withId($skuId);
        }

        $inventory->refill(Stock::create($command->content['stock']));

        $this->inventoryList->save($inventory);
    }
}
