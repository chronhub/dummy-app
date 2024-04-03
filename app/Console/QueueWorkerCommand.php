<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Process\Process;
use Throwable;

use function pcntl_async_signals;
use function sleep;
use function sprintf;

class QueueWorkerCommand extends Command implements SignalableCommandInterface
{
    private int $count = 5;

    private int $timeout = 5;

    private bool $shouldRun = true;

    private string $worker = 'queue:work';

    /**
     * @var Collection{Process}|null
     */
    private ?Collection $processes = null;

    protected $signature = 'shop:worker {num=5}';

    public function __invoke(): int
    {
        pcntl_async_signals(true);

        $this->count = (int) $this->argument('num');

        if ($this->count < 1) {
            $this->error('Number of workers must be greater than 0');

            return self::FAILURE;
        }

        $this->makeWorkers($this->count);

        try {
            $this->info(sprintf('Starting %d workers', $this->count));

            $this->monitorProcesses();
        } catch (Throwable $e) {
            $this->shouldRun = false;

            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function monitorProcesses(): void
    {
        while ($this->shouldRun) {
            $this->processes = $this->processes->filter(function (Process $process) {
                if (! $process->isRunning()) {
                    $process->stop();

                    return false;
                }

                return true;
            });

            $this->reProvisionWorkers();
        }

        $this->processes->each->stop();
    }

    private function reProvisionWorkers(): void
    {
        $current = $this->count - $this->processes->count();

        if ($current !== 0) {
            $this->line(sprintf('Provisioning %d workers', $current));
            $this->makeWorkers($current);
        }

        sleep($this->timeout);
    }

    private function makeWorkers(?int $times): void
    {
        if ($this->processes === null) {
            $this->processes = collect();
        }

        $this->processes = $this->processes->merge(collect()->times($times ?? $this->count, function () {
            return $this->makeWorker();
        }));
    }

    private function makeWorker(): Process
    {
        $process = new Process(['php', 'artisan', $this->worker, '--max-jobs=250']);

        $process->start();

        return $process;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal)
    {
        $this->shouldRun = false;

        return self::SUCCESS;
    }
}
