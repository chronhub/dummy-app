<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class CatalogItemModel
{
    private function __construct(
        public string $name,
        public string $description,
        public string $skuCode,
        public string $category,
        public string $brand,
        public string $model,
    ) {
    }

    public static function fromObject(object $catalogItem): self
    {
        return new self(
            $catalogItem->name,
            $catalogItem->description,
            $catalogItem->sku_code,
            $catalogItem->category,
            $catalogItem->brand,
            $catalogItem->model,
        );
    }
}
