<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use function base64_encode;
use function json_encode;

final class Sku
{
    public function __construct(
        public SkuId $skuId,
        public InventoryItemId $inventoryItemId,
        public Stock $stock,
        public UnitPrice $unitPrice,
        public InventoryItemInfo $inventoryItemInfo
    ) {
    }

    public function generateSku(): string
    {
        $name = 'SKU_'.$this->skuId->id->toBase58().'-';
        $name .= 'PRD_'.$this->inventoryItemId->id->toBase58().'-';
        $name .= 'VR_'.base64_encode(json_encode($this->inventoryItemInfo)).'-';
        $name .= 'ST_'.$this->stock->value.'-';
        $name .= 'UP_'.$this->unitPrice->value;

        return $name;
    }
}
