<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemQuantityIncreased;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Inventory implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private InventoryItemId $itemId;

    private Stock $stock;

    private UnitPrice $unitPrice;

    private int $reserved = 0;

    public static function add(SkuId $skuId, InventoryItemId $itemId, Stock $stock, UnitPrice $unitPrice): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, $itemId, $stock, $unitPrice));

        return $self;
    }

    public function increase(Stock $stock): void
    {
        $newStock = $this->stock->value + $stock->value;

        $this->recordThat(InventoryItemQuantityIncreased::withItem($this->skuId(), $this->itemId, Stock::create($newStock), $this->stock));
    }

    public function remove(Stock $stock): void
    {
        // sell
    }

    public function adjust(Stock $stock): void
    {
        // returns product
    }

    public function reserve(Stock $quantity): void
    {
        // order
    }

    public function release(Stock $quantity): void
    {
        // cancel order
    }

    public function skuId(): SkuId
    {
        return $this->identity;
    }

    public function itemId(): InventoryItemId
    {
        return $this->itemId;
    }

    public function stock(): Stock
    {
        return $this->stock;
    }

    public function unitPrice(): UnitPrice
    {
        return $this->unitPrice;
    }

    protected function applyInventoryItemAdded(InventoryItemAdded $event): void
    {
        $this->itemId = $event->inventoryItemId();
        $this->stock = $event->stock();
        $this->unitPrice = $event->unitPrice();
    }

    protected function applyInventoryItemQuantityIncreased(InventoryItemQuantityIncreased $event): void
    {
        $this->stock = $event->newStock();
    }
}
