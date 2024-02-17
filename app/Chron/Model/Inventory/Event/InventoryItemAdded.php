<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemAdded extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $initialStock, UnitPrice $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'initial_stock' => $initialStock->value,
            'unit_price' => $unitPrice->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function initialStock(): Stock
    {
        return Stock::create($this->content['initial_stock']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }
}
