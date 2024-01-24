<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Attribute\MessageHandler\MessageHandler;
use App\Chron\Reporter\Subscribers\ChainHandlerResolver;
use RuntimeException;

it('resolve sync and dispatch async handler one by one', function (): void {
    $handlers = [
        new MessageHandler('handler_2', fn () => 'foo', 2, null),
        new MessageHandler('handler_1', fn () => 'bar', 1, null),
        new MessageHandler('handler_4', fn () => 'redis', 4, ['connection' => 'redis']),
        new MessageHandler('handler_3', fn () => 'rabbitmq', 3, ['connection' => 'rabbitmq']),
    ];

    $firstDispatch = (new ChainHandlerResolver($handlers, []))->handle(false);

    expect($firstDispatch->getSyncHandlers())->toHaveCount(2)
        ->and($firstDispatch->getSyncHandlers()[0]->name())->toBe('handler_1')
        ->and($firstDispatch->getSyncHandlers()[1]->name())->toBe('handler_2')
        ->and($firstDispatch->getAsyncHandler())->toBeInstanceOf(MessageHandler::class)
        ->and($firstDispatch->getAsyncHandler()->name())->toBe('handler_3')
        ->and($firstDispatch->getAsyncHandler()->queue())->toBe(['connection' => 'rabbitmq']);

    $next = (new ChainHandlerResolver($handlers, $firstDispatch->getQueues()))->handle(true);

    expect($next->getSyncHandlers())->toHaveCount(1)
        ->and($next->getSyncHandlers()[0]->name())->toBe('handler_3')
        ->and($next->getAsyncHandler())->toBeInstanceOf(MessageHandler::class)
        ->and($next->getAsyncHandler()->name())->toBe('handler_4')
        ->and($next->getAsyncHandler()->queue())->toBe(['connection' => 'redis']);

    $third = (new ChainHandlerResolver($handlers, $next->getQueues()))->handle(true);

    expect($third->getSyncHandlers())->toHaveCount(1)
        ->and($third->getSyncHandlers()[0]->name())->toBe('handler_4')
        ->and($third->getAsyncHandler())->toBeNull();

    $exception = false;

    try {
        (new ChainHandlerResolver($handlers, $third->getQueues()))->handle(true);
    } catch (RuntimeException $e) {
        $exception = $e;
    }

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Queue already completed');
});

it('resolve async first and sync', function (): void {
    $handlers = [
        new MessageHandler('handler_2', fn () => 'foo', 2, null),
        new MessageHandler('handler_1', fn () => 'redis', 1, ['connection' => 'redis']),
    ];

    $firstDispatch = (new ChainHandlerResolver($handlers, []))->handle(false);

    expect($firstDispatch->getSyncHandlers())->toHaveCount(0)
        ->and($firstDispatch->getAsyncHandler())->toBeInstanceOf(MessageHandler::class)
        ->and($firstDispatch->getAsyncHandler()->name())->toBe('handler_1')
        ->and($firstDispatch->getAsyncHandler()->queue())->toBe(['connection' => 'redis']);

    $next = (new ChainHandlerResolver($handlers, $firstDispatch->getQueues()))->handle(true);

    expect($next->getSyncHandlers())->toHaveCount(2)
        ->and($next->getSyncHandlers()[0]->name())->toBe('handler_1')
        ->and($next->getSyncHandlers()[1]->name())->toBe('handler_2')
        ->and($next->getAsyncHandler())->toBeNull();

    $exception = false;

    try {
        (new ChainHandlerResolver($handlers, $next->getQueues()))->handle(true);
    } catch (RuntimeException $e) {
        $exception = $e;
    }

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Queue already completed');
});

it('resolve async one by one', function (): void {
    $handlers = [
        new MessageHandler('handler_2', fn () => 'foo', 2, ['connection' => 'rabbitmq']),
        new MessageHandler('handler_1', fn () => 'redis', 1, ['connection' => 'redis']),
    ];

    $firstDispatch = (new ChainHandlerResolver($handlers, []))->handle(false);

    expect($firstDispatch->getSyncHandlers())->toHaveCount(0)
        ->and($firstDispatch->getAsyncHandler())->toBeInstanceOf(MessageHandler::class)
        ->and($firstDispatch->getAsyncHandler()->name())->toBe('handler_1');

    $next = (new ChainHandlerResolver($handlers, $firstDispatch->getQueues()))->handle(true);

    expect($next->getSyncHandlers())->toHaveCount(1)
        ->and($next->getSyncHandlers()[0]->name())->toBe('handler_1')
        ->and($next->getAsyncHandler())->toBeInstanceOf(MessageHandler::class)
        ->and($next->getAsyncHandler()->name())->toBe('handler_2');

    $third = (new ChainHandlerResolver($handlers, $next->getQueues()))->handle(true);

    expect($third->getSyncHandlers())->toHaveCount(1)
        ->and($third->getSyncHandlers()[0]->name())->toBe('handler_2')
        ->and($third->getAsyncHandler())->toBeNull();

    $exception = false;

    try {
        (new ChainHandlerResolver($handlers, $third->getQueues()))->handle(true);
    } catch (RuntimeException $e) {
        $exception = $e;
    }

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Queue already completed');
});
