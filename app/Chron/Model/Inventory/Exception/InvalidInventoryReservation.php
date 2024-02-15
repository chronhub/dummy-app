<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Product\SkuId;

use function sprintf;

class InvalidInventoryReservation extends DomainException
{
    public static function mustBeGreaterThanZero(SkuId $skuId): self
    {
        return new self(sprintf('Reserve quantity in inventory must be greater than zero for item: %s', $skuId));
    }
}
