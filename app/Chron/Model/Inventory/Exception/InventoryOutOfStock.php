<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use App\Chron\Model\Inventory\SkuId;

use function sprintf;

class InventoryOutOfStock extends InventoryException
{
    public static function forSkuId(SkuId $skuId): self
    {
        return new self(sprintf('Inventory is out of stock for item: %s', $skuId->toString()));
    }
}
