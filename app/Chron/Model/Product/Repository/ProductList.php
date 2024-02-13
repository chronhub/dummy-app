<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Repository;

use App\Chron\Model\Product\Product;
use App\Chron\Model\Product\ProductId;

interface ProductList
{
    public function get(ProductId $productId): ?Product;

    public function save(Product $product): void;
}
