<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Attribute\BindReporterContainer;
use RuntimeException;

use function array_merge;
use function is_array;
use function is_object;
use function is_string;

class DetermineQueueHandler
{
    public function __construct(protected BindReporterContainer $container)
    {
    }

    public function make(string $reporterId, null|string|array|object $queue): ?array
    {
        if (is_string($queue)) {
            $queue = $this->container[$queue]->jsonSerialize();
        }

        if (is_object($queue)) {
            $queue = $queue->jsonSerialize();
        }

        return $this->resolveQueueFromReporter($reporterId, $queue);
    }

    protected function resolveQueueFromReporter(string $reporterId, ?array $queue): ?array
    {
        $config = $this->container->getQueues()[$reporterId] ?? null;

        // no config found, return the queue as is
        if (! is_array($config)) {
            return $queue;
        }

        $sync = $config['sync'] ?? true;

        // if sync is true, return the queue if it's not null
        if ($sync === true && $queue !== null) {
            return $queue;
        }

        if ($config['queue'] === []) {
            throw new RuntimeException("Config queue cannot be empty for reporter $reporterId");
        }

        // if sync is false, merge the queue with the config queue
        // do not define default queue if handlers queues must act as default
        return $queue === null ? $config['queue'] : array_merge($config['queue'], $queue);
    }
}
