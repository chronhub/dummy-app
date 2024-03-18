<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

final readonly class ProductInfo
{
    private function __construct(
        public string $name,
        public string $description,
        public string $category,
        public string $brand,
        public string $model,
    ) {
    }

    public static function create(
        string $name,
        string $description,
        string $category,
        string $brand,
        string $model,
    ): self {
        return new self(
            $name,
            $description,
            $category,
            $brand,
            $model,
        );
    }

    /**
     * @param array $data{name: string, description: string, category: string, brand: string, model: string}
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['description'],
            $data['category'],
            $data['brand'],
            $data['model'],
        );
    }

    public function sameValueAs(self $other): bool
    {
        return $this->name === $other->name
            && $this->description === $other->description
            && $this->category === $other->category
            && $this->brand === $other->brand
            && $this->model === $other->model;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'brand' => $this->brand,
            'model' => $this->model,
        ];
    }
}
