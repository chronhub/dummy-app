<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Process\Process;
use Throwable;

use function pcntl_async_signals;
use function sleep;
use function sprintf;

#[AsCommand(
    name: 'read-model:process',
    description: 'Process read model'
)]
final class ReadModelProcess extends Command implements SignalableCommandInterface
{
    protected $signature = 'read-model:process';

    private array $commands = [
        'customer' => 'customer:read-model',
        'inventory' => 'inventory:read-model',
        'product' => 'product:read-model',
        'cart' => 'cart:read-model',
        'cart_item' => 'cart-item:read-model',
        'catalog' => 'catalog:read-model',
    ];

    /**
     * @var Collection<Process>
     */
    private Collection $processes;

    private bool $shouldRun = true;

    public function __invoke(): int
    {
        pcntl_async_signals(true);

        try {
            $this->startProcesses();
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
            $this->processes->each(function (Process $process) {
                if (! $process->isRunning()) {
                    $this->shouldRun = false;
                    $this->line(sprintf('Processing %s is terminated', $process->getCommandLine()));
                }
            });

            sleep(30);
        }

        $this->stopProcesses();
    }

    private function startProcesses(): void
    {
        $this->processes = collect($this->commands)->map(function (string $command) {
            $process = new Process(['php', 'artisan', $command]);
            $process->start();

            $this->info('Starting '.$command);

            return $process;
        });
    }

    private function stopProcesses(): void
    {
        $this->processes->each(fn (Process $process) => $process->stop());
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal)
    {
        $this->info('Stopping processes...');

        $this->shouldRun = false;
    }
}
