<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use App\Chron\Model\Inventory\SkuId;

use function sprintf;

class InventoryItemAlreadyExists extends InventoryException
{
    public static function withId(SkuId $skuId): self
    {
        return new self(sprintf('Inventory item with sku id %s already exists', $skuId->toString()));
    }
}
