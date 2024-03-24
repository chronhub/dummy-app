<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class CatalogModel
{
    private function __construct(
        public string $id,
        public CatalogItemModel $item,
        public string $currentPrice,
        public ?string $oldPrice,
        public int $quantity,
        public string $reserved,
        public string $status,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public function fromObject(object $catalog): self
    {
        return new self(
            $catalog->id,
            CatalogItemModel::fromObject($catalog),
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
