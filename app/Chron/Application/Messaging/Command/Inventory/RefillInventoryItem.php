<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Inventory;

use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\SkuId;
use Storm\Message\AbstractDomainCommand;

final class RefillInventoryItem extends AbstractDomainCommand
{
    public static function withItem(string $skuId, int $quantity): self
    {
        return new self([
            'sku_id' => $skuId,
            'quantity' => $quantity,
        ]);
    }

    public function skuId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function quantity(): Quantity
    {
        return Quantity::create($this->content['quantity']);
    }
}
