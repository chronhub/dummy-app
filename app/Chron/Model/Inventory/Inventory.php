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

    private Stock $initialStock;

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
    public function refill(Stock $stock): void
    {
        $newStock = $this->stock->add($stock);

        $this->recordThat(InventoryItemRefilled::withItem($this->skuId(), $newStock, $this->stock));
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
    public function reserve(ReservationQuantity $requested): void
    {
        // todo refactor, increase reserved and let the stock untouched

        $availableQuantity = $this->stock->getAvailableQuantity($requested);

        if ($availableQuantity === false) {
            throw InventoryOutOfStock::forSkuId($this->skuId());
        }

        $newStock = $this->stock->remove($availableQuantity->toStock());
        $reserved = Reservation::create($availableQuantity->value);
        $totalReserved = $this->reserved->add($reserved);

        $availableQuantity->sameValueAs($requested)
            ? $this->recordItemReserved($newStock, $reserved, $requested)
            : $this->recordItemPartiallyReserved($newStock, $reserved, $requested);

        if ($newStock->isOutOfStock()) {
            $this->recordThat(InventoryItemExhausted::withItem($this->skuId(), $newStock, $totalReserved->toQuantity()));
        }
    }

    /**
     * Release the inventory item with a reason.
     */
    public function release(ReservationQuantity $requested, string $reason): void
    {
        // todo compensation
        if ($this->reserved->value < $requested->value) {
            throw new RuntimeException('Quantity in inventory to release is greater than reserved quantity');
        }

        $newStock = $this->stock->add($requested->toStock());
        $reserved = Reservation::create($requested->value);
        $totalReserved = $this->reserved->sub($reserved);

        $this->recordThat(InventoryItemReleased::withItem(
            $this->skuId(),
            $newStock,
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

    /**
     * Get available quantity for reservation
     *
     * return full or partial available quantity or false if not available
     */
    public function getAvailableQuantity(ReservationQuantity $requested): ReservationQuantity|false
    {
        return $this->stock->getAvailableQuantity($requested);
    }

    private function recordItemReserved(Stock $newStock, Reservation $reserved, ReservationQuantity $requested): void
    {
        $event = InventoryItemReserved::withItem(
            $this->skuId(),
            $newStock,
            $this->stock,
            $reserved->toQuantity(),
            $this->reserved->add($reserved)->toQuantity(),
            Quantity::create($requested->value)
        );

        $this->recordThat($event);
    }

    private function recordItemPartiallyReserved(Stock $newStock, Reservation $reserved, ReservationQuantity $requested): void
    {
        $event = InventoryItemPartiallyReserved::withItem(
            $this->skuId(),
            $newStock,
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
                $this->initialStock = $event->stock();
                $this->stock = $event->stock();
                $this->unitPrice = $event->unitPrice();
                $this->reserved = Reservation::create(0);

                break;
            case $event instanceof InventoryItemRefilled:
                $this->initialStock = $event->newStock();
                $this->stock = $event->newStock();

                break;
            case $event instanceof InventoryItemReserved:
                $this->stock = $event->newStock();
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;

            case $event instanceof InventoryItemPartiallyReserved:
                $this->stock = $event->newStock();
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;

            case $event instanceof InventoryItemReleased:
                $this->stock = $event->newStock();
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
