<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Product;

use Storm\Message\AbstractDomainCommand;

final class CreateProduct extends AbstractDomainCommand
{
    /**
     * @param array $productInfo{name: string, description: string, category: string, brand: string, model: string}
     */
    public static function withProduct(string $productId, array $productInfo): self
    {
        return new self([
            'product_id' => $productId,
            'product_info' => $productInfo,
        ]);
    }
}
