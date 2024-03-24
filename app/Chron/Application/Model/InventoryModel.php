<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

final readonly class InventoryModel
{
    private function __construct(
        public string $id,
        public string $price,
        public int $stock,
        public int $reserved,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(object $inventory): self
    {
        return new self(
            $inventory->id,
            $inventory->unit_price,
            $inventory->stock,
            $inventory->reserved,
            $inventory->created_at,
            $inventory->updated_at
        );
    }
}
