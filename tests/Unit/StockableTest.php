<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;
use App\Chron\Model\Inventory\InventoryStock;
use App\Chron\Model\Inventory\Quantity;

it('throws an exception if stock is negative', function () {
    InventoryStock::create(-1, 0);
})->throws(InvalidInventoryValue::class);

it('throws an exception if reservation is negative', function () {
    InventoryStock::create(1, -1);

})->throws(InvalidInventoryValue::class);

it('adds stock quantity', function () {
    $inventoryValue = InventoryStock::create(10, 0);
    $newInventoryValue = $inventoryValue->addStock(Quantity::create(5));

    expect($newInventoryValue->stock)->toBe(15)
        ->and($newInventoryValue->reserved)->toBe(0);
});

it('removes stock quantity', function () {
    $inventoryValue = InventoryStock::create(10, 0);
    $newInventoryValue = $inventoryValue->removeStock(Quantity::create(5));

    expect($newInventoryValue->stock)->toBe(5)
        ->and($newInventoryValue->reserved)->toBe(0);
});

it('adds reservation quantity', function () {
    $inventoryValue = InventoryStock::create(10, 0);
    $newInventoryValue = $inventoryValue->addReservation(Quantity::create(5));

    expect($newInventoryValue->stock)->toBe(10)
        ->and($newInventoryValue->reserved)->toBe(5);
});

it('removes reservation quantity', function () {
    $inventoryValue = InventoryStock::create(10, 5);
    $newInventoryValue = $inventoryValue->releaseReservation(Quantity::create(3));

    expect($newInventoryValue->stock)->toBe(10)
        ->and($newInventoryValue->reserved)->toBe(2);
});

it('checks if out of stock', function () {
    expect(InventoryStock::create(0, 0)->isOutOfStock())->toBeTrue()
        ->and(InventoryStock::create(5, 0)->isOutOfStock())->toBeFalse();
});

it('calculates available quantity', function () {
    $inventoryValue = InventoryStock::create(10, 5);

    expect($inventoryValue->getAvailableQuantity(Quantity::create(3)))->toBe(3)
        ->and($inventoryValue->getAvailableQuantity(Quantity::create(10)))->toBe(5)
        ->and($inventoryValue->getAvailableQuantity(Quantity::create(20)))->toBe(5);
});

it('calculates partial available quantity', function () {
    $inventoryValue = InventoryStock::create(10, 5);

    expect($inventoryValue->getAvailableQuantity(Quantity::create(10)))->toBe(5);
});

it('it return not available quantity', function () {
    $inventoryValue = InventoryStock::create(10, 10);

    expect($inventoryValue->getAvailableQuantity(Quantity::create(10)))->toBe(0)
        ->and($inventoryValue->getAvailableQuantity(Quantity::create(20)))->toBe(0);
});
