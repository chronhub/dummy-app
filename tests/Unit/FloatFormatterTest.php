<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\FloatFormatter;

it('formats float to string', function (float $value, string $expected) {
    $formatted = FloatFormatter::toString($value);

    expect($formatted)->toBe($expected);
})->with([
    [10.00, '10.00'],
    [10.0, '10.00'],
    [10.25, '10.25'],
    [10.50, '10.50'],
    [10.75, '10.75'],
    [10.755, '10.76'],
    [10.75555555, '10.76'],
]);

it('formats int to string', function () {
    $int = 10;

    $formatted = FloatFormatter::toString($int);

    expect($formatted)->toBe('10.00');
});

it('formats string to string', function () {
    $string = '10.00';

    $formatted = FloatFormatter::toString($string);

    expect($formatted)->toBe('10.00');
});

it('formats float to float', function () {
    $float = 10.00;

    $formatted = FloatFormatter::toFloat($float);

    expect($formatted)->toBe(10.00);
});

it('formats int to float', function () {
    $int = 10;

    $formatted = FloatFormatter::toFloat($int);

    expect($formatted)->toBe(10.00);
});

it('formats string to float', function () {
    $string = '10.00';

    $formatted = FloatFormatter::toFloat($string);

    expect($formatted)->toBe(10.00);
});

it('format zero to string', function () {
    $zero = 0;

    $formatted = FloatFormatter::toString($zero);

    expect($formatted)->toBe('0.00');
});

it('format zero to float', function () {
    $zero = 0;

    $formatted = FloatFormatter::toFloat($zero);

    expect($formatted)->toBe(0.00);
});

it('format negative to string', function () {
    $negative = -10;

    $formatted = FloatFormatter::toString($negative);

    expect($formatted)->toBe('-10.00');
});

it('format negative to float', function () {
    $negative = -10;

    $formatted = FloatFormatter::toFloat($negative);

    expect($formatted)->toBe(-10.00);
});
