<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Product;

use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\ProductInfo;
use Storm\Message\AbstractDomainCommand;

final class CreateProduct extends AbstractDomainCommand
{
    /**
     * @param array{name: string, description: string, category: string, brand: string, model: string} $productInfo
     */
    public static function withProduct(string $productId, array $productInfo): self
    {
        return new self([
            'product_id' => $productId,
            'product_info' => $productInfo,
        ]);
    }

    public function productId(): ProductId
    {
        return ProductId::fromString($this->content['product_id']);
    }

    public function productInfo(): ProductInfo
    {
        return ProductInfo::fromArray($this->content['product_info']);
    }
}
