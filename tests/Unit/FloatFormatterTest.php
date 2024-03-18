<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\InvalidPriceValue;
use App\Chron\Model\Price;

it('can validate a valid price', function (string $value) {
    expect(Price::fromString($value))->toBe($value);
})->with([
    ['0.00'],
    ['0.01'],
    ['0.99'],
    ['1.00'],
    ['1.01'],
    ['1.99'],
    ['10.00'],
    ['10.01'],
    ['10.99'],
    ['100.00'],
    ['100.01'],
    ['100.99'],
    ['1000.00'],
    ['1000.01'],
    ['1000.99'],
]);

it('raise exception when price has not two decimals', function (string $value) {
    Price::fromString($value);
})->with([
    ['0'],
    ['10'],
    ['10.1'],
    ['10.75555555'],
])->throws(InvalidPriceValue::class, 'Price value must be a decimal number with two decimals');

it('raise exception when price has a negative dash', function (string $value) {
    Price::fromString($value);
})->with([
    ['-1.00'],
    ['-1.01'],
    ['-0.00'],
    ['-0'],
])->throws(InvalidPriceValue::class, 'Price value must be a decimal number with two decimals');
