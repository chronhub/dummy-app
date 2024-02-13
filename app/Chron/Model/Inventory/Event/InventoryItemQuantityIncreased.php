<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\InventoryItemId;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemQuantityIncreased extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, InventoryItemId $itemId, Stock $newStock, Stock $oldStock): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'inventory_item_id' => $itemId->toString(),
            'new_stock' => $newStock->value,
            'old_stock' => $oldStock->value,
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

    public function newStock(): Stock
    {
        return Stock::create($this->content['new_stock']);
    }

    public function oldStock(): Stock
    {
        return Stock::create($this->content['old_stock']);
    }
}
