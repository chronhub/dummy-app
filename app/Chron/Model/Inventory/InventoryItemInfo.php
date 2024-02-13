<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

class InventoryItemInfo
{
    private function __construct(
        public string $name,
        public string $category,
        public string $brand,
        public string $model,
    ) {
    }

    public static function create(
        string $name,
        string $category,
        string $brand,
        string $model,
    ): self {
        return new self(
            $name,
            $category,
            $brand,
            $model,
        );
    }

    /**
     * @param array $data{name: string, category: string, brand: string, model: string}
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['category'],
            $data['brand'],
            $data['model'],
        );
    }

    public function sameValueAs(self $other): bool
    {
        return $this->name === $other->name
            && $this->category === $other->category
            && $this->brand === $other->brand
            && $this->model === $other->model;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'brand' => $this->brand,
            'model' => $this->model,
        ];
    }
}
