<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Inventory;

use Storm\Message\AbstractDomainCommand;

final class AddInventoryItem extends AbstractDomainCommand
{
    public static function withItem(string $skuId, string $productId, array $productInfo, int $stock, string $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId,
            'product_id' => $productId,
            'product_info' => $productInfo,
            'stock' => $stock,
            'unit_price' => $unitPrice,
        ]);
    }
}
