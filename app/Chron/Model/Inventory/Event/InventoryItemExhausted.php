<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemExhausted extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $availableStock, Quantity $reserved): self
    {
        return new self([
            'sku_id' => $skuId,
            'available_stock' => $availableStock->value,
            'quantity_reserved' => $reserved->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function newStock(): Stock
    {
        return Stock::create($this->content['available_stock']);
    }

    public function totalReserved(): Quantity
    {
        return Quantity::create($this->content['quantity_reserved']);
    }
}
