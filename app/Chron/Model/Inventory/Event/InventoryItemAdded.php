<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemAdded extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $availableStock, UnitPrice $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'available_stock' => $availableStock->value,
            'unit_price' => $unitPrice->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function availableStock(): Stock
    {
        return Stock::create($this->content['available_stock']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }
}
