<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use function sprintf;

class InsufficientQuantityForReservation extends InventoryException
{
    public static function withSkuId(string $skuId, int $quantity, int $availableQuantity): self
    {
        return new self(
            sprintf(
                'Insufficient quantity for reservation for sku "%s". Requested: %d, Available: %d',
                $skuId,
                $quantity,
                $availableQuantity
            )
        );
    }
}
