<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Inventory;

use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\UnitPrice;
use Storm\Message\AbstractDomainCommand;

final class AddInventoryItem extends AbstractDomainCommand
{
    public static function withItem(string $skuId, int $quantity, string $unitPrice): self
    {
        return new self([
            'sku_id' => $skuId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function skuId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function quantity(): PositiveQuantity
    {
        return PositiveQuantity::create($this->content['quantity']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }
}
