<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

use function base64_encode;
use function json_encode;

final class Sku
{
    public function __construct(
        public ProductId $productId,
        public ProductInfo $productInfo
    ) {
    }

    public function generateSku(): string
    {
        $code = 'SKU_'.$this->productId->id->toBase58().'-';
        $code .= 'VR_'.base64_encode(json_encode($this->productInfo));

        return $code;
    }
}
