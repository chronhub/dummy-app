<?php

declare(strict_types=1);

namespace App\Chron\Application\Console\Shop;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Application\Service\CustomerService;
use App\Chron\Package\Reporter\Report;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Uid\Uuid;

use function sleep;

#[AsCommand(
    name: 'shop:seed',
    description: 'Seed the shop'
)]
class SeedShop extends Command
{
    protected $signature = 'shop:seed';

    public function __invoke(): int
    {
        $this->call('migrate');

        $this->createProducts();

        $this->registerCustomer();

        return self::SUCCESS;
    }

    protected function createProducts(): void
    {

        Report::relay(
            CreateProduct::withProduct(
                Uuid::v4()->jsonSerialize(),
                [
                    'name' => 'Product 1',
                    'category' => 'Category 1',
                    'description' => fake()->sentence,
                    'brand' => fake()->company,
                    'model' => fake()->word,
                ]
            ));

        sleep(5);

        $this->registerOtherProducts();
    }

    protected function registerOtherProducts(): void
    {
        $i = 2;

        while ($i < 99) {
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
                ));

            $i++;
        }
    }

    protected function registerCustomer(): void
    {
        /** @var CustomerService $customerService */
        $customerService = $this->laravel[CustomerService::class];

        $customerService->registerCustomer();
    }
}
