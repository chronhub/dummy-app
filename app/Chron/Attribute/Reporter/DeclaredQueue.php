<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use Illuminate\Contracts\Container\Container;
use RuntimeException;

use function array_merge;
use function is_object;
use function is_string;

class DeclaredQueue
{
    /**
     * @param array<ReporterMode> $queues
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
     * @return array<ReporterMode>
     */
    public function getQueues(): array
    {
        return $this->queues;
    }

    public function getQueueById(string $reporterId): ?ReporterMode
    {
        return $this->queues[$reporterId] ?? null;
    }

    protected function resolve(string $reporterId, ?array $handlerQueue): ?array
    {
        $declared = $this->queues[$reporterId];

        // force sync even for handler would have queue defined
        if ($declared->enqueue->isSync()) {
            return null;
        }

        // force async all handlers to use default queue
        if ($declared->enqueue->isAsync()) {
            if ($declared->default === null) {
                throw new RuntimeException("Default queue cannot be null for reporter $reporterId when enqueue is async");
            }

            if ($handlerQueue !== null) {
                throw new RuntimeException("Queue cannot be defined for reporter $reporterId when enqueue is async");
            }

            return $this->mergeWithDefaultQueue($declared->default, null);
        }

        // delegate to handler queue but merge with only required default queue when queue exists
        if ($declared->enqueue->isDelegateMerge()) {
            return match (true) {
                $handlerQueue === null => null,
                $declared->default !== null => $this->mergeWithDefaultQueue($declared->default, $handlerQueue),
                default => throw new RuntimeException("Default queue cannot be null for reporter $reporterId when enqueue is delegate_merge_with_default")
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
