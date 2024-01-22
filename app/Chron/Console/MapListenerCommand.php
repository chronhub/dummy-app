<?php

declare(strict_types=1);

namespace App\Chron\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

use function sprintf;

#[AsCommand(
    name: 'reporter-listener:map',
    description: 'Get the listeners map by reporter name',
)]
class MapListenerCommand extends Command
{
    const TABLE_HEADERS = ['Event', 'Origin', 'Priority', 'Listener'];

    protected $signature = 'reporter-listener:map
                            { name?      : reporter name }
                            { --choice=1 : request for choice }';

    public function __invoke(): int
    {
        try {
            $reporterId = $this->handleCompletionName();

            $reporter = $this->getReporter($reporterId);
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

    protected function getReporter(string $reporterId): Reporter
    {
        return $this->laravel[$reporterId];
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

    protected function handleCompletionName(): string
    {
        $argumentName = $this->argument('name');

        if ($argumentName) {
            return $argumentName;
        }

        if ($this->option('choice') === '1') {
            $name = $this->components->choice('Find reporter by id',
                $this->flattenArray(config('reporter.reporter', []))
            );
        }

        return $name ?? throw new InvalidArgumentException('Reporter name not found or not provided');
    }

    protected function flattenArray(array $reporters): array
    {
        $result = [];

        foreach ($reporters as $reporter) {
            foreach ($reporter as $config) {
                if (isset($config['reporter'])) {
                    $result[] = $config['reporter'];
                }
            }
        }

        return $result;
    }
}
