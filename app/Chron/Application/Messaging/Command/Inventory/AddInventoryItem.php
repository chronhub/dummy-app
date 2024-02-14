<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Inventory;

use Storm\Message\AbstractDomainCommand;

final class AddInventoryItem extends AbstractDomainCommand
{
    public static function withItem(string $skuId, int $stock, string $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId,
            'stock' => $stock,
            'unit_price' => $unitPrice,
        ]);
    }
}
