<?php

declare(strict_types=1);

namespace App\Chron\Application\Console\Shop;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'shop:migrate',
    description: 'Shop Migration'
)]
final class MigrateShopCommand extends Command
{
    protected $signature = 'shop:migrate';

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

        return self::SUCCESS;
    }

    protected function createStream(): void
    {
        $connection = $this->laravel['db'];

        foreach ($this->streamNames as $streamName) {
            $connection->table('stream_event')->useWritePdo()->insert(['stream_name' => $streamName]);
        }
    }
}
