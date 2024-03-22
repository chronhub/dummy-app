<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;

final class ResetProjectionCommand extends Command
{
    protected $signature = 'projection:reset {projection}';

    private array $projections = [
        'customer',
        'product',
        'inventory',
        'order',
        'order_item',
        'cart',
        'cart_item',
        'catalog',
    ];

    public function __invoke(ProjectorManagerInterface $projectorManager): int
    {
        $name = $this->argument('projection');

        if ($name === 'all') {
            foreach ($this->projections as $projection) {
                $this->resetProjection($projectorManager, $projection);
            }

            return self::SUCCESS;
        }

        $this->resetProjection($projectorManager, $name);

        return self::SUCCESS;
    }

    private function resetProjection(ProjectorManagerInterface $projectorManager, string $name): void
    {
        $projectorManager->monitor()->markAsReset($name);
    }
}
