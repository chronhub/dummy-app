<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemExhausted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Inventory\Exception\InventoryOutOfStock;
use App\Chron\Model\Product\SkuId;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;
use Storm\Contract\Message\DomainEvent;

final class Inventory implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private Stock $stock;

    private UnitPrice $unitPrice;

    private Reservation $reserved;

    /**
     * Add unique inventory item with skuId, stock and unit price
     */
    public static function add(SkuId $skuId, Stock $stock, UnitPrice $unitPrice): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, $stock, $unitPrice));

        return $self;
    }

    /**
     * Refill the inventory item
     *
     * When product has been marked unavailable, and now it's refilled, adjusted.
     * Sole responsibility of the product management to get back the inventory item to the list of available items
     */
    public function refill(Quantity $quantity): void
    {
        $stock = $this->stock->add($quantity);

        $this->recordThat(InventoryItemRefilled::withItem($this->skuId(), $stock, $this->stock, $quantity));
    }

    /**
     * Adjust the inventory item.
     */
    public function adjust(Stock $stock): void
    {
        // returns product
    }

    /**
     * Reserve the inventory item.
     *
     * Can imply global or per sku rules
     *
     * @todo add rule for limit reservation in time
     * @todo add rule for limit reservation in quantity
     * @todo add rule to send notification when stock is low
     */
    public function reserve(Quantity $requested): void
    {
        // todo refactor, increase reserved and let the stock untouched

        $availableQuantity = $this->stock->getAvailableQuantity($requested);

        if ($availableQuantity === false) {
            throw InventoryOutOfStock::forSkuId($this->skuId());
        }

        $availableStock = $this->stock->remove($availableQuantity);
        $reserved = Reservation::create($availableQuantity->value);
        $totalReserved = $this->reserved->add($reserved);

        $availableQuantity->sameValueAs($requested)
            ? $this->recordItemReserved($availableStock, $reserved, $requested)
            : $this->recordItemPartiallyReserved($availableStock, $reserved, $requested);

        if ($availableStock->isOutOfStock()) {
            $this->recordThat(InventoryItemExhausted::withItem($this->skuId(), $availableStock, $totalReserved->toQuantity()));
        }
    }

    /**
     * Release the inventory item with a reason.
     */
    public function release(Quantity $requested, string $reason): void
    {
        // todo compensation
        if ($this->reserved->value < $requested->value) {
            throw new RuntimeException('Quantity in inventory to release is greater than reserved quantity');
        }

        $availableStock = $this->stock->add($requested);
        $reserved = Reservation::create($requested->value);
        $totalReserved = $this->reserved->sub($reserved);

        $this->recordThat(InventoryItemReleased::withItem(
            $this->skuId(),
            $availableStock,
            $this->stock,
            $reserved->toQuantity(),
            $totalReserved->toQuantity(),
            Quantity::create($requested->value),
            $reason
        ));
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
    public function getAvailableQuantity(Quantity $requested): Quantity|false
    {
        return $this->stock->getAvailableQuantity($requested);
    }

    private function recordItemReserved(Stock $availableStock, Reservation $reserved, Quantity $requested): void
    {
        $event = InventoryItemReserved::withItem(
            $this->skuId(),
            $availableStock,
            $this->stock,
            $reserved->toQuantity(),
            $this->reserved->add($reserved)->toQuantity(),
            Quantity::create($requested->value)
        );

        $this->recordThat($event);
    }

    private function recordItemPartiallyReserved(Stock $availableStock, Reservation $reserved, Quantity $requested): void
    {
        $event = InventoryItemPartiallyReserved::withItem(
            $this->skuId(),
            $availableStock,
            $this->stock,
            $reserved->toQuantity(),
            $this->reserved->add($reserved)->toQuantity(),
            Quantity::create($requested->value)
        );

        $this->recordThat($event);
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof InventoryItemAdded:
                $this->stock = $event->availableStock();
                $this->unitPrice = $event->unitPrice();
                $this->reserved = Reservation::create(0);

                break;
            case $event instanceof InventoryItemRefilled:
                $this->stock = $event->newStock();

                break;
            case $event instanceof InventoryItemReserved:
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;

            case $event instanceof InventoryItemPartiallyReserved:
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;

            case $event instanceof InventoryItemReleased:
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;
            case $event instanceof InventoryItemExhausted:
                $this->stock = $event->newStock();
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
