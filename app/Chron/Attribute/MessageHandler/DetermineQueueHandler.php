<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Attribute\BindReporterContainer;
use RuntimeException;

use function array_merge;
use function is_array;
use function is_object;

class DetermineQueueHandler
{
    public function __construct(protected BindReporterContainer $container)
    {
    }

    public function make(string $reporterId, null|array|object $queue): ?array
    {
        if (is_object($queue)) {
            $queue = $queue->jsonSerialize();
        }

        return $this->resolveQueueFromReporter($reporterId, $queue);
    }

    protected function resolveQueueFromReporter(string $reporterId, ?array $queue): ?array
    {
        // sync : sync,async,delegate

        // if sync, force sync even for handler would have queue defined
        // if async, force async even for handler would not have queue defined required default queue
        // if delegate, handler queue is used if defined


        $config = $this->container->getQueues()[$reporterId] ?? null;

        // no default config defined, return the queue as is
        if (! is_array($config)) {
            return $queue;
        }

        $sync = $config['sync'];

        // if sync is true, return the handler queue as is
        if ($sync === true) {
            return $queue;
        }

        $reporterQueue = $config['queue'] ?? null;

        if (blank($reporterQueue)) {
            throw new RuntimeException("Config queue cannot be empty for reporter $reporterId");
        }

        // if sync is false, merge the queue with the config queue
        // do not define default queue if handlers queues must act as default
        return $queue === null ? $config['queue'] : array_merge($config['queue'], $queue);
    }
}
