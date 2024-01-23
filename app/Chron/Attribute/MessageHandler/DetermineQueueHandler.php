<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;

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

    protected function makeQueueFromReporter(string $reporterId, ?array $queue): ?array
    {
        $config = $this->searchReporterConfig($reporterId);

        $configQueue = $config['queue'] ?? null;

        if ($configQueue === null) {
            return $queue;
        }

        $userQueue = $this->container[$configQueue['default']]->jsonSerialize();

        $async = $configQueue['async'] ?? false;

        if ($async === true && $queue === null) {
            return $userQueue;
        }

        return array_merge($userQueue, $queue);
    }

    protected function makeQueueConfiguration(?array $queue): ?array
    {
        $defaultQueue = Arr::get($this->config, 'queue.default');

        if (is_string($defaultQueue) && is_array($queue)) {
            return array_merge($this->container[$defaultQueue]->jsonSerialize(), $queue);
        }

        return $queue;
    }

    protected function searchReporterConfig(string $reporterId): array
    {
        foreach ($this->config['reporter'] as $reporter) {
            foreach ($reporter as $config) {
                if ($config['id'] === $reporterId) {
                    return $config;
                }
            }
        }

        throw new InvalidArgumentException(sprintf('Configuration not found for reporter id %s', $reporterId));
    }
}
