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
        /** @var ProductApplicationService $productService */
        $productService = $this->laravel[ProductApplicationService::class];

        $productService->createProducts();
    }

    protected function registerCustomer(): void
    {
        /** @var CustomerApplicationService $customerService */
        $customerService = $this->laravel[CustomerApplicationService::class];

        $customerService->registerCustomers(1);
    }
}
