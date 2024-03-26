<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\Inventory\InventoryStock;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Stock;

it('creates inventory stock', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    expect($inventoryStock->stock->value)->toBe(10)
        ->and($inventoryStock->reserved->value)->toBe(5);
});

it('adds stock', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $inventoryStock = $inventoryStock->addStock(PositiveQuantity::create(5));

    expect($inventoryStock->stock->value)->toBe(15);
});

it('removes stock', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $inventoryStock = $inventoryStock->removeStock(PositiveQuantity::create(5));

    expect($inventoryStock->stock->value)->toBe(5);
});

it('adds reservation', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $inventoryStock = $inventoryStock->addReservation(PositiveQuantity::create(5));

    expect($inventoryStock->reserved->value)->toBe(10);
});

it('releases reservation', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $inventoryStock = $inventoryStock->releaseReservation(PositiveQuantity::create(5));

    expect($inventoryStock->reserved->value)->toBe(0);
});

it('checks if out of stock', function () {
    $stock = Stock::create(0);
    $reserved = Quantity::create(0);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    expect($inventoryStock->isOutOfStock())->toBeTrue();
});

it('gets available quantity', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $availableQuantity = $inventoryStock->getAvailableQuantity(PositiveQuantity::create(5));

    expect($availableQuantity->value)->toBe(5);
});

it('gets available quantity 2', function () {
    $stock = Stock::create(1);
    $reserved = Quantity::create(1);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $availableQuantity = $inventoryStock->getAvailableQuantity(PositiveQuantity::create(1));

    expect($availableQuantity->value)->toBe(0);
});

it('gets available stock', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);
    $inventoryStock = InventoryStock::create($stock, $reserved);

    $availableStock = $inventoryStock->getAvailableStock();

    expect($availableStock->value)->toBe(5);
});
