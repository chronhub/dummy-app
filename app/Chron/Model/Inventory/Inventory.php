<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Product\Sku;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Inventory implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private Sku $sku;

    private int $reserved = 0;

    public static function add(SkuId $skuId, InventoryItemId $itemId, Stock $stock, UnitPrice $unitPrice): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, $itemId, $stock, $unitPrice));

        return $self;
    }

    public function increase(Stock $stock): void
    {
        // restock
    }

    public function decrease(Stock $stock): void
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

    public function sku(): Sku
    {
        return clone $this->sku;
    }

    protected function applyInventoryItemAdded(InventoryItemAdded $event): void
    {
        $this->sku = $event->sku();
    }
}
