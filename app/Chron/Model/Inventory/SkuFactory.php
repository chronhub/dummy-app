<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;

class SkuFactory
{
    public static function createFromCommand(SkuId $skuId, AddInventoryItem $command): Sku
    {
        return new Sku(
            $skuId,
            InventoryItemId::fromString($command->content['product_id']),
            Stock::create($command->content['stock']),
            UnitPrice::create($command->content['unit_price']),
            InventoryItemInfo::fromArray($command->content['product_info'])
        );
    }
}
