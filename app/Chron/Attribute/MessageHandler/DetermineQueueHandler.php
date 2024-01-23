<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

use function array_merge;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

class DetermineQueueHandler
{
    protected array $config;

    public function __construct(protected Container $container)
    {
        $this->config = $this->container['config']->get('reporter');
    }

    public function make(string $reporterId, null|string|array|object $queue): ?array
    {
        if (is_string($queue)) {
            return $this->container[$queue]->jsonSerialize();
        }

        if (is_object($queue)) {
            return $queue->jsonSerialize();
        }

        return $this->makeQueueFromReporter($reporterId, $queue) ?? $this->makeQueueConfiguration($queue);
    }

    protected function makeQueueFromReporter(string $reporterId, null|string|array|object $queue): ?array
    {
        $reporterQueue = Arr::get($this->config, sprintf('reporter.%s.queue', $reporterId));

        if (is_array($reporterQueue) && isset($reporterQueue['default'])) {
            $userQueue = $this->container[$reporterQueue['default']]->jsonSerialize();

            $async = $reporterQueue['async'] ?? false;

            if ($async && $queue === null) {
                return $userQueue;
            }

            return array_merge($userQueue, $queue);
        }

        return null;
    }

    protected function makeQueueConfiguration(null|string|array|object $queue): ?array
    {
        $defaultQueue = Arr::get($this->config, 'queue.default');

        if (is_string($defaultQueue) && is_array($queue)) {
            return array_merge($this->container[$defaultQueue]->jsonSerialize(), $queue);
        }

        return $queue;
    }
}
