<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\Order\Amount;
use App\Chron\Model\Order\Balance;
use InvalidArgumentException;

it('should create a new balance', function () {
    $balance = Balance::newInstance();

    expect($balance->value())->toBe('0.00');
});

it('format integer value', function (string $amount) {
    $balance = Balance::fromString($amount);

    expect($balance->value())->toBe($amount.'.00');
})->with(['10', '100']);

it('should add decimal amount to the balance', function (string $amount) {
    $balance = Balance::newInstance();
    $balance->add(Amount::fromString($amount));

    expect($balance->value())->toBe($amount);
})->with(['10.00', '10.55']);

it('should add many amounts to the balance', function (string $amount, string $expected) {
    $balance = Balance::newInstance();
    $balance->add(Amount::fromString($amount));
    $balance->add(Amount::fromString($amount));

    expect($balance->value())->toBe($expected);
})->with([
    ['10.00', '20.00'],
    ['10', '20.00'],
    ['100', '200.00'],
]);

it('return float value', function (string $amount) {
    $balance = Balance::fromString($amount);

    expect($balance->toFloat())->toBe(10.00);
})->with(['10.00', '10']);

it('return string value', function (string $amount) {
    $balance = Balance::fromString($amount);

    expect($balance->value())->toBe($amount);
})->with(['10.00', '10.44']);

it('should compare positively two balances', function (string $amount) {
    $balance = Balance::fromString($amount);
    $balance2 = Balance::fromString($amount);

    expect($balance->sameValueAs($balance2))->toBeTrue();
})->with(['10.00', '10']);

it('should compare two different balances', function () {
    $balance = Balance::fromString('10');
    $balance2 = Balance::fromString('20.12');

    expect($balance->sameValueAs($balance2))->toBeFalse();
});

it('should throw an exception when the balance is negative', function () {
    Balance::fromString('-10.00');
})->throws(InvalidArgumentException::class, 'Balance must a positive number.');
