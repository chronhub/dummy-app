<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\MessageHandler\MessageHandlerEntry;
use App\Chron\Attribute\TagContainer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_map;

#[AsCommand(
    name: 'reporter-message:map',
    description: 'Get the messages map by message name',
)]
class MapMessageCommand extends Command
{
    // todo filter headers / type
    // todo add a vertical table when message is requested
    const TABLE_HEADERS = ['Reporter', 'Type', 'Message', 'Tag', 'Handler class', 'Handler method', 'Handler priority', 'Queue'];

    protected $signature = 'reporter-message:map
                            { --message= : Message name either full or short class name }
                            { --ask=1    : Ask for complete message name }
                            { --short=1  : Short class base name output }';

    public function __invoke(TagContainer $tagContainer): int
    {
        /** @var Collection<string, MessageHandlerEntry> $map */
        $map = collect($tagContainer->map);

        if ($this->option('ask') === '1') {
            $shortKeys = $map->keys()->map(fn (string $key): string => $this->shortClass($key))->toArray();

            $messageName = $this->components->askWithCompletion('filter by short message name?', $shortKeys);
        } else {
            $messageName = $this->option('message');
        }

        $messages = $this->findInMap($map, $messageName);

        if ($messageName && $messages->isEmpty()) {
            $this->components->error('Message name not found in map for '.$messageName);

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
            ->map(fn (array $handlers): array => $handlers);
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
        return array_map(fn (MessageHandlerEntry $handler): array => [
            $handler->data->reporterId,
            $handler->data->type->value,
            $this->shortClass($messageName),
            $handler->messageId,
            //$handler->messageHandlerId,
            $this->shortClass($handler->data->reflectionClass->getName()),
            $handler->data->handlerMethod,
            $handler->data->priority,
            $this->formatQueue($handler->queue),
        ], $handlers);
    }

    protected function shortClass(string $class): string
    {
        return ($this->option('short') === '0') ? $class : class_basename($class);
    }

    protected function formatQueue(?object $queue): string
    {
        return $queue === null ? 'sync or unknown' : $queue::class;
    }
}
