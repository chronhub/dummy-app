<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Repository;

use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Product\SkuId;

interface InventoryList
{
    public function get(SkuId $skuId): ?Inventory;

    public function save(Inventory $inventory): void;
}
