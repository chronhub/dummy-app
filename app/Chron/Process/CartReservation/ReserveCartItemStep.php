<?php

declare(strict_types=1);

namespace App\Chron\Process\CartReservation;

use App\Chron\Application\Messaging\Command\Cart\StartAddCartItem;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Throwable;

final readonly class ReserveCartItemStep implements SagaStep
{
    public function __construct(private InventoryReservationService $inventoryReservationService)
    {
    }

    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof StartAddCartItem;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof StartAddCartItem) {
            return;
        }

        $quantity = $event->toContent()['cart_item_quantity'];
        $sku = $event->toContent()['cart_item_sku'];

        $this->inventoryReservationService->reserveItem($sku, $quantity);

        logger()->info('Item reserved', [
            'sku' => $sku,
            'quantity' => $quantity,
        ]);
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {
        logger()->error('Item reservation failed', [
            'exception' => $exception?->getMessage() ?? 'Unknown error',
        ]);
    }
}
