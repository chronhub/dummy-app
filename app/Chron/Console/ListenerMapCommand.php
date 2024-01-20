<?php

declare(strict_types=1);

namespace App\Chron\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

use function sprintf;

#[AsCommand(
    name: 'reporter-listener:map',
    description: 'Get the listeners map by reporter name',
)]
class ListenerMapCommand extends Command
{
    const TABLE_HEADERS = ['Event', 'Origin', 'Priority', 'Listener'];

    protected $signature = 'reporter-listener:map
                            { name : reporter name }';

    public function __invoke(): int
    {
        try {
            $reporter = $this->getReporter();
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $listeners = $reporter->tracker()->listeners();

        // todo prettier
        $this->components->twoColumnDetail(
            sprintf('ReporterClass: %s', $reporter::class),
            sprintf('Total: %d', $listeners->count())
        );

        $this->table(self::TABLE_HEADERS, $this->formatTableData($listeners));

        return self::SUCCESS;
    }

    protected function getReporter(): Reporter
    {
        $name = $this->argument('name');

        return $this->laravel[$name];
    }

    protected function formatTableData(Collection $listeners): array
    {
        return $listeners
            ->sortByDesc(fn (Listener $listener): int => $listener->priority())
            ->map(fn (Listener $listener) => [
                $listener->name(),
                $listener->origin(),
                $listener->priority(),
                $listener::class,
            ])->toArray();
    }
}
