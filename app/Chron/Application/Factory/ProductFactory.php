<?php

declare(strict_types=1);

namespace App\Chron\Application\Factory;

use Generator;

use function number_format;

/**
 * @template T of array{category: string, brand: string, description: string, model: string, name: string}
 */
final class ProductFactory
{
    /**
     * @return array{T}
     */
    public static function makeProduct(int $i): array
    {
        return [
            'name' => 'Product '.$i,
            'category' => 'Category '.$i,
            'description' => fake()->sentence,
            'brand' => fake()->company,
            'model' => fake()->word,
        ];
    }

    /**
     * @return Generator<array{T}>
     */
    public static function makeProducts(int $times = 100): Generator
    {
        for ($i = 1; $i <= $times; $i++) {
            yield self::makeProduct($i);
        }
    }

    /**
     * @return array{string, int, string}
     */
    public static function makeProductItem(string $skuId): array
    {
        return [
            $skuId,
            self::createProductQuantity(),
            self::createRandomPrice(),
        ];
    }

    /**
     * @return positive-int
     */
    public static function createProductQuantity(): int
    {
        return fake()->numberBetween(500, 5000);
    }

    private static function createRandomPrice(): string
    {
        $unitPrice = fake()->randomFloat(2, 10, 2000);

        return number_format($unitPrice, 2, '.', '');
    }
}
