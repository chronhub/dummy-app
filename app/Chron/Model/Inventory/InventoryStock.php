<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;

use function max;
use function min;

final readonly class InventoryStock
{
    private function __construct(
        public Stock $stock,
        public Quantity $reserved
    ) {
        $this->guardAgainstInvalidValues();
    }

    public static function create(Stock $stock, Quantity $reserved): self
    {
        return new self($stock, $reserved);
    }

    public function addStock(Quantity $quantity): self
    {
        $newStock = Stock::create($this->stock->value + $quantity->value);

        return new self($newStock, $this->reserved);
    }

    public function removeStock(Quantity $quantity): self
    {
        $newStock = Stock::create($this->stock->value - $quantity->value);

        return new self($newStock, $this->reserved);
    }

    public function addReservation(Quantity $quantity): self
    {
        $newReserved = Quantity::create($this->reserved->value + $quantity->value);

        return new self($this->stock, $newReserved);
    }

    public function releaseReservation(Quantity $quantity): self
    {
        $newReserved = Quantity::create($this->reserved->value - $quantity->value);

        return new self($this->stock, $newReserved);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock->value === 0;
    }

    public function getAvailableQuantity(Quantity $requested): Quantity
    {
        $availableStock = $this->stock->value - $this->reserved->value;

        return Quantity::create(max(0, min($requested->value, $availableStock)));
    }

    public function getAvailableStock(): Stock
    {
        $availableStock = $this->stock->value - $this->reserved->value;

        return Stock::create($availableStock);
    }

    private function guardAgainstInvalidValues(): void
    {
        if ($this->stock->value < 0) {
            throw new InvalidInventoryValue('Inventory stock must be greater than or equal to 0');
        }

        if ($this->reserved->value < 0) {
            throw new InvalidInventoryValue('Inventory reservation must be greater or equal to 0');
        }

        if ($this->stock->value < $this->reserved->value) {
            throw new InvalidInventoryValue('Inventory stock must be greater than or equal to reservation');
        }
    }
}
