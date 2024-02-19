<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\Inventory\InventoryStock;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Stock;

it('calculates available quantity', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);

    $inventoryValue = InventoryStock::create($stock, $reserved);

    expect($inventoryValue->getAvailableQuantity(PositiveQuantity::create(3)))->toBe(3)
        ->and($inventoryValue->getAvailableQuantity(PositiveQuantity::create(10)))->toBe(5)
        ->and($inventoryValue->getAvailableQuantity(PositiveQuantity::create(20)))->toBe(5);
});

//with available quantity of 5, and requested quantity of 7, it should return 5
it('calculates partial available quantity', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(5);

    $inventoryValue = InventoryStock::create($stock, $reserved);

    expect($inventoryValue->getAvailableQuantity(PositiveQuantity::create(7))->value)->toBe(5);
});

it('it return not available quantity', function () {
    $stock = Stock::create(10);
    $reserved = Quantity::create(10);

    $inventoryValue = InventoryStock::create($stock, $reserved);

    expect($inventoryValue->getAvailableQuantity(PositiveQuantity::create(7))->value)->toBe(0);
});
