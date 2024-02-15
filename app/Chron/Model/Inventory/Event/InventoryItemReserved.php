<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemReserved extends AbstractDomainEvent
{
    public static function withItem(
        SkuId $skuId,
        Stock $newStock,
        Stock $oldStock,
        Quantity $reserved,
        Quantity $totalReserved,
        Quantity $requested
    ): self {
        return new self([
            'sku_id' => $skuId->toString(),
            'new_stock' => $newStock->value,
            'old_stock' => $oldStock->value,
            'reserved' => $reserved->value,
            'total_reserved' => $totalReserved->value,
            'requested' => $requested->value,
        ]);
    }

    public function aggregateId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function newStock(): Stock
    {
        return Stock::create($this->content['new_stock']);
    }

    public function oldStock(): Stock
    {
        return Stock::create($this->content['old_stock']);
    }

    public function reserved(): Quantity
    {
        return Quantity::create($this->content['reserved']);
    }

    public function totalReserved(): Quantity
    {
        return Quantity::create($this->content['total_reserved']);
    }

    public function quantityRequested(): Quantity
    {
        return Quantity::create($this->content['requested']);
    }
}
