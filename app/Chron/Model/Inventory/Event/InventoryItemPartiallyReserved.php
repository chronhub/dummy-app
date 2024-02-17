<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemPartiallyReserved extends AbstractDomainEvent
{
    public static function withItem(
        SkuId $skuId,
        Stock $availableStock,
        Stock $initialStock,
        Quantity $reserved,
        Quantity $totalReserved,
        Quantity $requested,
    ): self {
        return new self([
            'sku_id' => $skuId->toString(),
            'available_stock' => $availableStock->value,
            'initial_stock' => $initialStock->value,
            'quantity_reserved' => $reserved->value,
            'total_reserved' => $totalReserved->value,
            'quantity_requested' => $requested->value,
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

    public function initialStock(): Stock
    {
        return Stock::create($this->content['initial_stock']);
    }

    public function reserved(): Quantity
    {
        return Quantity::create($this->content['quantity_reserved']);
    }

    public function totalReserved(): Quantity
    {
        return Quantity::create($this->content['total_reserved']);
    }

    public function quantityRequested(): Quantity
    {
        return Quantity::create($this->content['quantity_requested']);
    }
}
