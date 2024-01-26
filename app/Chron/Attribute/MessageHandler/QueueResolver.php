<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Attribute\Reporter\Enqueue;
use App\Chron\Attribute\ReporterContainer;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

use function array_merge;
use function is_object;
use function is_string;

class QueueResolver
{
    public function __construct(
        protected ReporterContainer $reporterContainer,
        protected Container $container
    ) {
    }

    public function make(string $reporterId, null|string|array|object $queue): ?array
    {
        if (is_object($queue)) {
            $queue = $queue->jsonSerialize();
        }

        if (is_string($queue)) {
            $queue = $this->container[$queue]->jsonSerialize();
        }

        return $this->resolveQueue($reporterId, $queue);
    }

    protected function resolveQueue(string $reporterId, ?array $queue): ?array
    {
        [$defaultQueue, $enqueue] = $this->getDeclaredQueue($reporterId);

        // force sync even for handler would have queue defined
        if ($enqueue->isSync()) {
            return null;
        }

        // force async even for handler would not have queue defined, required default queue
        if ($enqueue->isAsync()) {
            if ($defaultQueue === null) {
                throw new RuntimeException("Default queue cannot be null for reporter $reporterId when enqueue is async");
            }

            return $queue === null ? $defaultQueue : array_merge($defaultQueue, $queue);
        }

        // delegate to handler queue but merge with default queue when queue exists
        if ($enqueue->isDelegateMerge()) {
            if ($queue === null) {
                return null;
            }

            if ($defaultQueue === null) {
                throw new RuntimeException("Default queue cannot be null for reporter $reporterId when enqueue is delegate_merge_with_default");
            }

            return array_merge($defaultQueue, $queue);
        }

        // handler queue is used if defined
        return $queue;
    }

    /**
     * @return array{0: null|array, 1: Enqueue}
     */
    protected function getDeclaredQueue(string $reporterId): array
    {
        $config = $this->reporterContainer->getQueues()[$reporterId];

        return [$config['default_queue'], Enqueue::from($config['enqueue'])];
    }
}
