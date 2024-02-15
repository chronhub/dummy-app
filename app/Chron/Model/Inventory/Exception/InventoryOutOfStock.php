<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Product\SkuId;

use function sprintf;

class InventoryOutOfStock extends DomainException
{
    public static function forSkuId(SkuId $skuId): self
    {
        return new self(sprintf('Inventory is out of stock for this item: %s', $skuId->toString()));
    }
}
