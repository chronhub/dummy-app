<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Model\Inventory\Event\InventoryItemExhausted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Inventory\Exception\InventoryOutOfStock;
use RuntimeException;
use Storm\Aggregate\AggregateBehaviorTrait;
use Storm\Contract\Aggregate\AggregateIdentity;
use Storm\Contract\Aggregate\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

final class Inventory implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private InventoryStock $inventoryStock;

    private UnitPrice $unitPrice;

    /**
     * Add unique inventory item with skuId, stock and unit price
     */
    public static function add(SkuId $skuId, PositiveQuantity $quantity, UnitPrice $unitPrice): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, Stock::create($quantity->value), $unitPrice));

        return $self;
    }

    /**
     * Refill the inventory item.
     *
     * When product has been marked unavailable, and now it's refilled, adjusted.
     * Sole responsibility of the product management to get back the inventory item to the list of available items
     */
    public function refill(PositiveQuantity $quantity): void
    {
        $inventoryStock = $this->inventoryStock->addStock($quantity);

        $this->recordThat(InventoryItemRefilled::withItem(
            $this->skuId(),
            $inventoryStock->getAvailableStock(),
            $inventoryStock->stock,
            $quantity,
            $inventoryStock->reserved
        ));
    }

    /**
     * Remove and release the quantity of the inventory item.
     */
    public function adjust(PositiveQuantity $quantity): void
    {
        $this->release($quantity, InventoryReleaseReason::RESERVATION_CONFIRMED);

        $inventoryStock = $this->inventoryStock->removeStock($quantity);

        $this->recordThat(InventoryItemAdjusted::withItem(
            $this->skuId(),
            $inventoryStock->getAvailableStock(),
            $inventoryStock->stock,
            $quantity,
            $inventoryStock->reserved
        ));

        $this->handleOutOfStock($inventoryStock);
    }

    /**
     * Reserve the inventory item.
     *
     * Can imply global or per sku rules
     *
     * @todo add rule for limit reservation in time
     * @todo add rule for limit reservation in quantity
     */
    public function reserve(PositiveQuantity $requested): void
    {
        $availableQuantity = $this->inventoryStock->getAvailableQuantity($requested);

        if ($availableQuantity->value === 0) {
            throw InventoryOutOfStock::withSkuId($this->skuId());
        }

        $availableQuantity = $availableQuantity->toPositiveQuantity();

        $inventoryStock = $this->inventoryStock->addReservation($availableQuantity);

        if ($requested->value > $availableQuantity->value) {
            $this->recordItemPartiallyReserved($inventoryStock, $availableQuantity, $requested);
        } else {
            $this->recordItemReserved($inventoryStock, $availableQuantity, $requested);
        }

        $this->handleOutOfStock($inventoryStock);
    }

    /**
     * Release the inventory item with a reason.
     */
    public function release(PositiveQuantity $requested, string $reason): void
    {
        // todo compensation
        if ($this->inventoryStock->reserved->value < $requested->value) {
            throw new RuntimeException('Quantity in inventory to release is less than reserved quantity');
        }

        $inventoryStock = $this->inventoryStock->releaseReservation($requested);

        $this->recordThat(InventoryItemReleased::withItem(
            $this->skuId(),
            $inventoryStock->getAvailableStock(),
            $inventoryStock->stock,
            $requested,
            $inventoryStock->reserved,
            $reason
        ));
    }

    private function handleOutOfStock(InventoryStock $inventoryStock): void
    {
        // need context depends on reservation
        if ($inventoryStock->isOutOfStock()) {
            $this->recordThat(InventoryItemExhausted::withItem($this->skuId(), $inventoryStock->stock, $inventoryStock->reserved));
        }
    }

    public function skuId(): SkuId
    {
        /** @var AggregateIdentity&SkuId $identity */
        $identity = $this->identity();

        return $identity;
    }

    public function unitPrice(): UnitPrice
    {
        return $this->unitPrice;
    }

    /**
     * Get available quantity for reservation
     *
     * return full or partial available quantity or false if not available
     */
    public function determineAvailableQuantity(PositiveQuantity $requested): PositiveQuantity|false
    {
        $availableQuantity = $this->inventoryStock->getAvailableQuantity($requested);

        return $availableQuantity->value === 0 ? false : $availableQuantity->toPositiveQuantity();
    }

    public function getAvailableStock(): Stock
    {
        return $this->inventoryStock->getAvailableStock();
    }

    public function isOutOfStock(): bool
    {
        return $this->inventoryStock->isOutOfStock();
    }

    private function recordItemReserved(InventoryStock $newStock, PositiveQuantity $reserved, PositiveQuantity $requested): void
    {
        $event = InventoryItemReserved::withItem(
            $this->skuId(),
            $newStock->getAvailableStock(),
            $newStock->stock,
            $reserved,
            $requested,
            $newStock->reserved,
        );

        $this->recordThat($event);
    }

    private function recordItemPartiallyReserved(InventoryStock $newStock, PositiveQuantity $reserved, PositiveQuantity $requested): void
    {
        $event = InventoryItemPartiallyReserved::withItem(
            $this->skuId(),
            $newStock->getAvailableStock(),
            $newStock->stock,
            $reserved,
            $requested,
            $newStock->reserved,
        );

        $this->recordThat($event);
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof InventoryItemAdded:
                $this->inventoryStock = InventoryStock::create($event->totalStock(), Quantity::create(0));
                $this->unitPrice = $event->unitPrice();

                break;

            case $event instanceof InventoryItemAdjusted:
                $this->inventoryStock = InventoryStock::create($event->totalStock(), $event->totalReserved());

                break;
            case $event instanceof InventoryItemRefilled:
            case $event instanceof InventoryItemReserved:
            case $event instanceof InventoryItemPartiallyReserved:
            case $event instanceof InventoryItemReleased:
            case $event instanceof InventoryItemExhausted:
                $this->inventoryStock = InventoryStock::create($event->totalStock(), $event->totalReserved());

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
