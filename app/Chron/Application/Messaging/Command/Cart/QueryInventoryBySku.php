<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Cart;

use App\Chron\Model\Inventory\SkuId;

final readonly class QueryInventoryBySku
{
    public function __construct(private string $skuId)
    {
    }

    public function skuId(): SkuId
    {
        return SkuId::fromString($this->skuId);
    }
}
