<?php

declare(strict_types=1);

namespace App\Chron\Application\Console\Shop;

use App\Chron\Application\Service\CustomerApplicationService;
use App\Chron\Application\Service\ProductApplicationService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'shop:seed',
    description: 'Seed the shop'
)]
class SeedShopCommand extends Command
{
    protected $signature = 'shop:seed';

    public function __invoke(): int
    {
        $this->createProducts();

        $this->registerCustomer();

        return self::SUCCESS;
    }

    protected function createProducts(): void
    {
        /** @var ProductApplicationService $productService */
        $productService = $this->laravel[ProductApplicationService::class];

        $productService->createProducts();
    }

    protected function registerCustomer(): void
    {
        /** @var CustomerApplicationService $customerService */
        $customerService = $this->laravel[CustomerApplicationService::class];

        $customerService->registerCustomers(2);
    }
}
