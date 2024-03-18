<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Inventory\Service\InventoryReservationService;

final readonly class CartReservation
{
    public function __construct(private InventoryReservationService $reservationService)
    {
    }

    /**
     * @return int<0, max>
     */
    public function reserveItem(string $sku, int $quantity): int
    {
        $reserved = $this->reservationService->reserveItem($sku, $quantity);

        if ($reserved === false) {
            return 0;
        }

        return $reserved->value;
    }

    public function releaseItem(string $sku, int $quantity, string $reason): void
    {
        $this->reservationService->releaseItem($sku, $quantity, $reason);
    }
}
