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
        if ($this->stock->value < $this->reserved->value) {
            throw new InvalidInventoryValue('Inventory stock must be greater than or equal to reservation');
        }
    }

    public static function create(Stock $stock, Quantity $reserved): self
    {
        return new self($stock, $reserved);
    }

    public function addStock(PositiveQuantity $quantity): self
    {
        $newStock = Stock::create($this->stock->value + $quantity->value);

        return new self($newStock, $this->reserved);
    }

    public function removeStock(PositiveQuantity $quantity): self
    {
        $newStock = Stock::create($this->stock->value - $quantity->value);

        return new self($newStock, $this->reserved);
    }

    public function addReservation(PositiveQuantity $quantity): self
    {
        $newReserved = Quantity::create($this->reserved->value + $quantity->value);

        return new self($this->stock, $newReserved);
    }

    public function releaseReservation(PositiveQuantity $quantity): self
    {
        $newReserved = Quantity::create($this->reserved->value - $quantity->value);

        return new self($this->stock, $newReserved);
    }

    public function isOutOfStock(): bool
    {
        if ($this->stock->value === 0) {
            return true;
        }

        return $this->stock->value === $this->reserved->value;
    }

    public function getAvailableQuantity(PositiveQuantity $requested): Quantity
    {
        $availableStock = $this->getAvailableStock();

        $availableQuantity = max(0, min($requested->value, $availableStock->value));

        return Quantity::create($availableQuantity);
    }

    public function getAvailableStock(): Stock
    {
        $availableStock = $this->stock->value - $this->reserved->value;

        return Stock::create($availableStock);
    }
}
