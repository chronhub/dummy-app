<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Service;

interface OrderReservationService
{
    /**
     * @return int The number of items reserved.
     */
    public function reserveItem(string $skuId, string $productId, int $quantity): int;

    public function releaseItem(string $skuId, string $productId, int $quantity): bool;
}
