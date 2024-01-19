<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\TagContainer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_map;

#[AsCommand(
    name: 'reporter-message:map',
    description: 'Get the messages map by message name',
)]
class MessageMapCommand extends Command
{
    const TABLE_HEADERS = ['Message', 'Reporter', 'Id', 'Class', 'Method', 'Priority', 'Queue'];

    protected $signature = 'reporter-message:map
                            { --message= : Message name either full or short class name }
                            { --short=1  : Short class base name output }';

    public function __invoke(TagContainer $tagContainer): int
    {
        $map = collect($tagContainer->map);

        $message = $this->option('message');

        $messages = $this->findInMap($map, $message);

        if ($message && $messages->isEmpty()) {
            $this->error('Message name not found in map for '.$message);

            return self::FAILURE;
        }

        $this->table(self::TABLE_HEADERS, $this->formatTableData($messages));

        return self::SUCCESS;
    }

    protected function findInMap(Collection $messages, ?string $message): Collection
    {
        if ($message === null) {
            return $messages;
        }

        return $messages
            ->filter(fn (array $handlers, string $messageName): bool => $messageName === $message || class_basename($messageName) === $message)
            ->map(fn (array $handlers, string $messageName): array => $handlers);
    }

    protected function formatTableData(Collection $messages): array
    {
        return $messages
            ->map(fn (array $handlers, string $messageName): array => $this->formatHandler($handlers, $messageName))
            ->collapse()
            ->toArray();
    }

    protected function formatHandler(array $handlers, string $messageName): array
    {
        return array_map(fn (array $handler): array => [
            $this->shortClass($messageName),
            $handler['reporter_id'],
            $handler['handler_id'],
            $this->shortClass($handler['handler_class']),
            $handler['handler_method'],
            $handler['priority'],
            $this->formatQueue($handler['queue']),
        ], $handlers);
    }

    protected function shortClass(string $class): string
    {
        return ($this->option('short') === '0') ? $class : class_basename($class);
    }

    protected function formatQueue(?object $queue): string
    {
        if ($queue === null) {
            return 'sync or unknown';
        }

        return $queue::class;
    }
}
