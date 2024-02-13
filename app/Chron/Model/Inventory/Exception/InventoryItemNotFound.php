<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Product\SkuId;

use function sprintf;

class InventoryItemNotFound extends DomainException
{
    public static function withId(SkuId $skuId): self
    {
        return new self(sprintf('Inventory item with sku id %s not found', $skuId->toString()));
    }
}
