<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;

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

    public function refill(Stock $stock): void
    {
        $newStock = $this->stock->value + $stock->value;

        $this->recordThat(InventoryItemRefilled::withItem($this->skuId(), $this->itemId, Stock::create($newStock), $this->stock));
    }

    public function remove(Stock $stock): void
    {

    }

    public function adjust(Stock $stock): void
    {
        // returns product
    }

    public function reserve(Stock $stock): void
    {
        if ($this->stock->value < $stock->value) {
            throw new RuntimeException('Not enough stock');
        } else {
            $newStock = Stock::create($this->stock->value - $stock->value);

            $this->recordThat(InventoryItemReserved::withItem($this->skuId(), $this->itemId, $newStock, $stock));
        }
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

    public function reserved(): int
    {
        return $this->reserved;
    }

    protected function applyInventoryItemAdded(InventoryItemAdded $event): void
    {
        $this->itemId = $event->inventoryItemId();
        $this->stock = $event->stock();
        $this->unitPrice = $event->unitPrice();
    }

    protected function applyInventoryItemRefilled(InventoryItemRefilled $event): void
    {
        $this->stock = $event->newStock();
    }

    protected function applyInventoryItemReserved(InventoryItemReserved $event): void
    {
        $this->stock = $event->newStock();
        $this->reserved = $event->reserved()->value;
    }
}
