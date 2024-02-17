<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Event;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\Stock;
use Storm\Message\AbstractDomainEvent;

final class InventoryItemRefilled extends AbstractDomainEvent
{
    public static function withItem(SkuId $skuId, Stock $newStock, Stock $oldStock, Quantity $quantityRefilled): self
    {
        return new self([
            'sku_id' => $skuId->toString(),
            'new_stock' => $newStock->value,
            'old_stock' => $oldStock->value,
            'quantity_refilled' => $quantityRefilled->value,
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

    public function quantityRefilled(): Quantity
    {
        return Quantity::create($this->content['quantity_refilled']);
    }
}
