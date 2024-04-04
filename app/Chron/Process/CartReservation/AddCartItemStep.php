<?php

declare(strict_types=1);

namespace App\Chron\Process\CartReservation;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Application\Messaging\Command\Cart\StartAddCartItem;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Storm\Support\Facade\Report;
use Throwable;

final class AddCartItemStep implements SagaStep
{
    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof StartAddCartItem;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof StartAddCartItem) {
            return;
        }

        Report::relay(AddCartItem::toCart(
            $event->toContent()['cart_id'],
            $event->toContent()['cart_owner'],
            $event->toContent()['cart_item_sku'],
            $event->toContent()['cart_item_price'],
            $event->toContent()['cart_item_quantity']
        ));
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {
        logger()->error('Item addition failed', [
            'exception' => $exception?->getMessage() ?? 'Unknown error',
        ]);
    }
}
