<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\Stock;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemRefilled extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $availableStock, Stock $totalStock, PositiveQuantity $quantityRefilled): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'available_stock' => $availableStock->value,
            'total_stock' => $totalStock->value,
            'quantity_refilled' => $quantityRefilled->value,
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

    public function quantityRefilled(): PositiveQuantity
    {
        return PositiveQuantity::create($this->content['quantity_refilled']);
    }
}
