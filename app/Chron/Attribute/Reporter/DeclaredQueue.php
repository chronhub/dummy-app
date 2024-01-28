<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use Illuminate\Contracts\Container\Container;
use RuntimeException;

use function array_merge;
use function is_object;
use function is_string;
use function sprintf;

class DeclaredQueue
{
    public const ERROR_DEFAULT_QUEUE_NOT_DEFINED_FOR_ASYNC = 'Default queue cannot be null for reporter %s when mode is async';

    public const ERROR_QUEUE_DEFINED_FOR_ASYNC = 'Handler queue cannot be defined for reporter %s when mode is async';

    public const ERROR_DEFAULT_QUEUE_NOT_DEFINED = 'Default queue cannot be null for reporter %s when mode is delegate to merge';

    /**
     * @param array<ReporterQueue> $queues
     */
    public function __construct(
        protected array $queues,
        protected Container $container
    ) {
    }

    public function mergeIfNeeded(string $reporterId, null|string|array|object $queue): ?array
    {
        if (is_object($queue)) {
            $queue = $queue->jsonSerialize();
        }

        if (is_string($queue)) {
            $queue = $this->container[$queue]->jsonSerialize();
        }

        return $this->resolve($reporterId, $queue);
    }

    /**
     * @return array<ReporterQueue>
     */
    public function getQueues(): array
    {
        return $this->queues;
    }

    public function getQueueById(string $reporterId): ?ReporterQueue
    {
        return $this->queues[$reporterId] ?? null;
    }

    protected function resolve(string $reporterId, ?array $handlerQueue): ?array
    {
        $declared = $this->queues[$reporterId];

        // force sync even for handler would have queue defined
        if ($declared->mode->isSync()) {
            return null;
        }

        // force async all handlers to use default queue
        if ($declared->mode->isAsync()) {
            if ($declared->default === null) {
                throw new RuntimeException(sprintf(self::ERROR_DEFAULT_QUEUE_NOT_DEFINED_FOR_ASYNC, $reporterId));
            }

            if ($handlerQueue !== null) {
                throw new RuntimeException(sprintf(self::ERROR_QUEUE_DEFINED_FOR_ASYNC, $reporterId));
            }

            return $this->mergeWithDefaultQueue($declared->default, null);
        }

        // delegate to handler queue but merge with only required default queue when queue exists
        if ($declared->mode->isDelegateMerge()) {
            return match (true) {
                $handlerQueue === null => null,
                $declared->default !== null => $this->mergeWithDefaultQueue($declared->default, $handlerQueue),
                default => throw new RuntimeException(sprintf(self::ERROR_DEFAULT_QUEUE_NOT_DEFINED, $reporterId))
            };
        }

        // delegate to handler queue
        return $handlerQueue;
    }

    protected function mergeWithDefaultQueue(string|array $defaultQueue, ?array $handlerQueue): array
    {
        if (is_string($defaultQueue)) {
            $defaultQueue = $this->container[$defaultQueue]->jsonSerialize();
        }

        return array_merge($defaultQueue, $handlerQueue ?? []);
    }
}
