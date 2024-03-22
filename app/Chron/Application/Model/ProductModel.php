<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class ProductModel
{
    private function __construct(
        public string $id,
        public string $sku,
        public string $name,
        public string $description,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(object $product): self
    {
        return new ProductModel(
            $product->id,
            $product->sku_code,
            $product->name,
            $product->description,
            $product->created_at,
            $product->updated_at
        );
    }
}
