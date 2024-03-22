<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;

class QueueWorker extends Command
{
    private int $workers;

    protected $signature = 'shop:worker --max-jobs=1000';

    public function __invoke(): int
    {
        return self::SUCCESS;
    }
}
