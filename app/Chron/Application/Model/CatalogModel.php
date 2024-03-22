<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class CatalogModel
{
    private function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $sku,
        public string $category,
        public string $brand,
        public string $model,
        public string $currentPrice,
        public string $oldPrice,
        public int $quantity,
        public string $reserved,
        public string $status,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public function fromObject(object $catalog): self
    {
        return new CatalogModel(
            $catalog->id,
            $catalog->name,
            $catalog->description,
            $catalog->sku_code,
            $catalog->category,
            $catalog->brand,
            $catalog->model,
            $catalog->current_price,
            $catalog->old_price,
            $catalog->quantity,
            $catalog->reserved,
            $catalog->status,
            $catalog->created_at,
            $catalog->updated_at
        );
    }
}
