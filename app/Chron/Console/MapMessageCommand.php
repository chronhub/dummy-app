<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\Kernel;
use App\Chron\Attribute\Messaging\MessageAttribute;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_map;

#[AsCommand(
    name: 'reporter-message:map',
    description: 'Get the message map',
)]
class MapMessageCommand extends Command
{
    protected ?Kernel $attributeContainer = null;

    // todo filter headers / type
    // todo add a vertical table when message is requested
    const TABLE_HEADERS = ['Reporter', 'Type', 'Message', 'Handler class', 'Handler method', 'Queue', 'Enqueue'];

    protected $signature = 'reporter-message:map
                            { --message= : Message name either full or short class name }
                            { --ask=1    : Ask for complete message name }
                            { --short=1  : Short class base name output }';

    public function __invoke(): int
    {
        $entries = $this->kernel()->messaging();

        $messageName = $this->requestMessageName($entries);

        $messages = $this->findInMap($entries, $messageName);

        if ($messageName && $messages->isEmpty()) {
            $this->components->error('Message name not found in map for '.$messageName);

            return self::FAILURE;
        }

        $this->table(self::TABLE_HEADERS, $this->formatTableData($messages));

        return self::SUCCESS;
    }

    protected function findInMap(Collection $messages, ?string $message): Collection
    {
        return $messages
            ->when($message !== null, fn (Collection $messages): Collection => $this->filterByMessage($messages, $message))
            ->map(fn (array $handlers): array => $handlers);
    }

    protected function filterByMessage(Collection $messages, string $message): Collection
    {
        return $messages->where(
            fn (array $handlers, string $messageName): bool => $messageName === $message || class_basename($messageName) === $message
        );
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
        return array_map(fn (MessageAttribute $handler): array => [
            $handler->reporterId,
            $handler->type,
            $this->shortClass($messageName),
            //$handler->messageId,
            //$handler->handlerId,
            $this->shortClass($handler->handlerClass),
            $this->formatHandlerMethod($handler->handlerMethod, $handler->priority),
            $this->formatQueue($handler->queue),
            $this->formatEnqueue($handler->reporterId),
        ], $handlers);
    }

    protected function shortClass(string $class): string
    {
        return ($this->option('short') === '0') ? $class : class_basename($class);
    }

    protected function formatHandlerMethod(string $method, int $priority): string
    {
        return "P$priority ....".$method;
    }

    protected function formatQueue(?array $queue): string
    {
        if ($queue === null) {
            return 'sync';
        }

        $async = 'async';

        if (isset($queue['connection'])) {
            $async .= ':'.$queue['connection'];
        }

        if (isset($queue['name'])) {
            $async .= '('.$queue['name'].')';
        }

        return $async;
    }

    protected function formatEnqueue(string $reporterId): string
    {
        $config = $this->kernel()->getDeclaredQueues()->getQueueById($reporterId);

        $format = $config->mode->value;

        return $format.($config->default !== null ? ': with default' : ': no default');
    }

    protected function requestMessageName(Collection $entries): ?string
    {
        $messageName = null;

        if ($this->option('ask') === '1') {
            $shortClasses = $entries->keys()->map(fn (string $class): string => $this->shortClass($class))->toArray();

            $messageName = $this->components->askWithCompletion('filter by short message name?', $shortClasses);
        }

        if ($messageName === null) {
            return $this->option('message');
        }

        return $messageName;
    }

    protected function kernel(): Kernel
    {
        return $this->attributeContainer ??= $this->laravel[Kernel::class];
    }
}
