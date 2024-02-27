<?php

declare(strict_types=1);

namespace App\Chron\Application\Console\Shop;

use App\Chron\Application\Service\CustomerService;
use App\Chron\Application\Service\ProductService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'shop:seed',
    description: 'Seed the shop'
)]
class SeedShop extends Command
{
    protected $signature = 'shop:seed';

    protected array $streamNames = [
        'customer',
        'product',
        'inventory',
        'cart',
        'order',
    ];

    public function __invoke(): int
    {
        $this->call('migrate');

        $this->createStream();

        $this->createProducts();

        $this->registerCustomer();

        return self::SUCCESS;
    }

    protected function createStream(): void
    {
        $connection = $this->laravel['db'];

        foreach ($this->streamNames as $streamName) {
            $connection->table('stream_event')->useWritePdo()->insert(['stream_name' => $streamName]);
        }
    }

    protected function createProducts(): void
    {
        /** @var ProductService $productService */
        $productService = $this->laravel[ProductService::class];

        $productService->createProducts();
    }

    protected function registerCustomer(): void
    {
        /** @var CustomerService $customerService */
        $customerService = $this->laravel[CustomerService::class];

        $customerService->registerCustomers(1);
    }
}
