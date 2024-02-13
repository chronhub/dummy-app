<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\InventoryItemId;
use App\Chron\Model\Inventory\InventoryItemInfo;
use App\Chron\Model\Inventory\Sku;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Inventory\UnitPrice;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemAdded extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Sku $sku): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'sku_code' => $sku->generateSku(),
            'inventory_item_id' => $sku->inventoryItemId->toString(),
            'inventory_item_info' => $sku->inventoryItemInfo->toArray(),
            'stock' => $sku->stock->value,
            'unit_price' => $sku->unitPrice->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function skuCode(): string
    {
        return $this->content['sku_code'];
    }

    public function inventoryItemId(): InventoryItemId
    {
        return InventoryItemId::fromString($this->content['inventory_item_id']);
    }

    public function inventoryItemInfo(): InventoryItemInfo
    {
        return InventoryItemInfo::fromArray($this->content['inventory_item_info']);
    }

    public function stock(): Stock
    {
        return Stock::create($this->content['stock']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }

    public function sku(): Sku
    {
        return new Sku(
            $this->aggregateId(),
            $this->inventoryItemId(),
            $this->stock(),
            $this->unitPrice(),
            $this->inventoryItemInfo()
        );
    }
}
