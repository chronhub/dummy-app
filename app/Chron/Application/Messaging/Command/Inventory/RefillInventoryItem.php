<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Inventory;

use Storm\Message\AbstractDomainCommand;

final class RefillInventoryItem extends AbstractDomainCommand
{
    public static function withItem(string $skuId, string $productId, int $stock): self
    {
        return new self([
            'sku_id' => $skuId,
            'product_id' => $productId,
            'stock' => $stock,
        ]);
    }
}
