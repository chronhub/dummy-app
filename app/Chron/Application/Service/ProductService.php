<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Package\Reporter\Report;
use Symfony\Component\Uid\Uuid;

final readonly class ProductService
{
    public function createProducts(): void
    {
        $i = 1;

        while ($i <= 100) {
            Report::relay(
                CreateProduct::withProduct(
                    Uuid::v4()->jsonSerialize(),
                    [
                        'name' => 'Product '.$i,
                        'category' => 'Category '.$i,
                        'description' => fake()->sentence,
                        'brand' => fake()->company,
                        'model' => fake()->word,
                    ]
                )
            );

            $i++;
        }
    }
}
