<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\InventoryItemId;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemAdded extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, InventoryItemId $itemId, Stock $stock, UnitPrice $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'inventory_item_id' => $itemId->toString(),
            'stock' => $stock->value,
            'unit_price' => $unitPrice->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function inventoryItemId(): InventoryItemId
    {
        return InventoryItemId::fromString($this->content['inventory_item_id']);
    }

    public function stock(): Stock
    {
        return Stock::create($this->content['stock']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }
}
