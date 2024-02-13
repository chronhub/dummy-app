<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Inventory implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private Sku $sku;

    private int $reserved = 0;

    public static function add(SkuId $skuId, Sku $sku): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, $sku));

        return $self;
    }

    public function increase(Stock $stock): void
    {

    }

    public function decrease(Stock $stock): void
    {

    }

    public function adjust(Stock $stock): void
    {

    }

    public function reserve(Stock $quantity): void
    {

    }

    public function release(Stock $quantity): void
    {

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
