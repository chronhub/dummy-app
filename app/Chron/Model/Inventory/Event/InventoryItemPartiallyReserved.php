<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\Stock;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemPartiallyReserved extends AbstractDomainEvent
{
    public static function withItem(
        SkuId $skuId,
        Stock $availableStock,
        Stock $totalStock,
        PositiveQuantity $reserved,
        PositiveQuantity $requested,
        Quantity $totalReserved,
    ): self {
        return new self([
            'sku_id' => $skuId->toString(),
            'available_stock' => $availableStock->value,
            'total_stock' => $totalStock->value,
            'quantity_reserved' => $reserved->value,
            'quantity_requested' => $requested->value,
            'total_reserved' => $totalReserved->value,
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

    public function totalStock(): Stock
    {
        return Stock::create($this->content['total_stock']);
    }

    public function reserved(): PositiveQuantity
    {
        return PositiveQuantity::create($this->content['quantity_reserved']);
    }

    public function requested(): PositiveQuantity
    {
        return PositiveQuantity::create($this->content['quantity_requested']);
    }

    public function totalReserved(): Quantity
    {
        return Quantity::create($this->content['total_reserved']);
    }
}
