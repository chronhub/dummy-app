<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;
use Storm\Contract\Message\DomainEvent;

use function sprintf;

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
        $newStock = $this->stock->add($stock);

        $this->recordThat(InventoryItemRefilled::withItem($this->skuId(), $this->itemId, $newStock, $this->stock));
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
        if (! $this->stock->isFullyAvailable($stock)) {
            throw new RuntimeException('Not enough stock. partial reservation is not supported yet.');
        }

        // todo partial reservation

        $this->recordThat(InventoryItemReserved::withItem($this->skuId(), $this->itemId, $this->stock->remove($stock), $stock));

        // if stock is less than ?, send notification
    }

    public function release(Stock $quantity): void
    {
        // cancel order
    }

    public function skuId(): SkuId
    {
        /** @var AggregateIdentity&SkuId $identity */
        $identity = $this->identity();

        return $identity;
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

    public function canReserve(Stock $stock): bool
    {
        return $this->stock->isFullyAvailable($stock);
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof InventoryItemAdded:
                $this->itemId = $event->inventoryItemId();
                $this->stock = $event->stock();
                $this->unitPrice = $event->unitPrice();

                break;
            case $event instanceof InventoryItemRefilled:
                $this->stock = $event->newStock();

                break;
            case $event instanceof InventoryItemReserved:
                $this->stock = $event->newStock();
                $this->reserved = $this->reserved + $event->reserved()->value;

                break;
            default:
                throw new RuntimeException(sprintf('Unknown inventory event %s', $event::class));
        }
    }
}
