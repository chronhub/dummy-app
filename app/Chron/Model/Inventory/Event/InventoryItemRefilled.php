<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\Stock;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemRefilled extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $availableStock, Stock $initialStock, Quantity $quantityRefilled): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'available_stock' => $availableStock->value,
            'initial_stock' => $initialStock->value,
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

    public function initialStock(): Stock
    {
        return Stock::create($this->content['initial_stock']);
    }

    public function quantityRefilled(): Quantity
    {
        return Quantity::create($this->content['quantity_refilled']);
    }
}
