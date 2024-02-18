<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Package\Reporter\Report;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final readonly class ProductService
{
    public function createProducts(): void
    {
        $i = 1;

        $categories = $this->createCategories();

        while ($i <= 100) {
            Report::relay(
                CreateProduct::withProduct(
                    Uuid::v4()->jsonSerialize(),
                    [
                        'name' => 'Product '.$i,
                        'category' => $categories[array_rand($categories)],
                        'description' => fake()->sentence,
                        'brand' => fake()->company,
                        'model' => fake()->word,
                    ]
                )
            );

            $i++;
        }
    }

    /**
     * @return array<int, string>
     */
    private function createCategories(): array
    {
        $categories = [];

        $i = 1;
        while ($i <= 10) {
            $categories[] = 'Category '.$i;

            $i++;
        }

        return $categories;
    }
}
