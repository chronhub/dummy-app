<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemExhausted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemRefilled;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Inventory\Exception\InventoryOutOfStock;
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

    private Stock $stock;

    private UnitPrice $unitPrice;

    private Reservation $reserved;

    public static function add(SkuId $skuId, Stock $stock, UnitPrice $unitPrice): self
    {
        $self = new self($skuId);

        $self->recordThat(InventoryItemAdded::withItem($skuId, $stock, $unitPrice));

        return $self;
    }

    public function refill(Stock $stock): void
    {
        $newStock = $this->stock->add($stock);

        $this->recordThat(InventoryItemRefilled::withItem($this->skuId(), $newStock, $this->stock));

        // when product has been marked unavailable, and now it's refilled, adjusted ...
        // its responsibility of the product to get back the inventory item to the list of available items
    }

    public function remove(ReservationQuantity $requested): void
    {
        // on purchase
    }

    public function adjust(Stock $stock): void
    {
        // returns product
    }

    public function reserve(ReservationQuantity $requested): void
    {
        // both todo can imply global rules or per sku rules
        // todo add rule for limit reservation in time
        // todo add rule for limit reservation in quantity
        // todo add rule to send notification when stock is low

        $availableQuantity = $this->stock->getAvailableQuantity($requested);

        if ($availableQuantity === false) {
            throw InventoryOutOfStock::forSkuId($this->skuId());
        }

        $newStock = $this->stock->remove($availableQuantity->toStock());
        $reserved = Reservation::create($availableQuantity->value);
        $totalReserved = $this->reserved->add($reserved);

        $availableQuantity->sameValueAs($requested)
            ? $this->recordReserveItem($newStock, $reserved, $requested)
            : $this->recordPartiallyReserveItem($newStock, $reserved, $requested);

        if ($newStock->isOutOfStock()) {
            // record event but still need to consider pending reservation
            $this->recordThat(InventoryItemExhausted::withItem($this->skuId(), $newStock, $totalReserved->toQuantity()));

            // todo add InventoryItemFullyExhausted when reserved is empty
            // handler should publish event to put the product in the out-of-stock list
            // and should delete the item from read inventory
        }
    }

    public function release(ReservationQuantity $requested): void
    {
        // cancel/return order
    }

    public function skuId(): SkuId
    {
        /** @var AggregateIdentity&SkuId $identity */
        $identity = $this->identity();

        return $identity;
    }

    public function stock(): Stock
    {
        return $this->stock;
    }

    public function unitPrice(): UnitPrice
    {
        return $this->unitPrice;
    }

    public function reserved(): Reservation
    {
        return clone $this->reserved;
    }

    /**
     * Get available quantity for reservation
     *
     * return full or partial available quantity
     * return false if not available
     */
    public function getAvailableQuantity(ReservationQuantity $requested): ReservationQuantity|false
    {
        return $this->stock->getAvailableQuantity($requested);
    }

    private function recordReserveItem(Stock $newStock, Reservation $reserved, ReservationQuantity $requested): void
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

    private function recordPartiallyReserveItem(Stock $newStock, Reservation $reserved, ReservationQuantity $requested): void
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
                $this->stock = $event->stock();
                $this->unitPrice = $event->unitPrice();
                $this->reserved = Reservation::create(0);

                break;
            case $event instanceof InventoryItemRefilled:
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

            case $event instanceof InventoryItemExhausted:
                $this->stock = $event->newStock();
                $this->reserved = Reservation::create($event->totalReserved()->value);

                break;
            default:
                throw new RuntimeException(sprintf('Unknown inventory event %s', $event::class));
        }
    }
}
