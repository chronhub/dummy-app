<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;

final class ResetProjectionCommand extends Command
{
    protected $signature = 'projection:reset {projection}';

    public function __invoke(ProjectorManagerInterface $projectorManager): int
    {
        $name = $this->argument('projection');

        $projectorManager->monitor()->markAsReset($name);

        return self::SUCCESS;
    }
}
