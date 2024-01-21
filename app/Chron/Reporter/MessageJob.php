<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\InteractsWithQueue;
use Storm\Contract\Message\Header;

class MessageJob
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The maximum number of unhandled exceptions to allow before failing
     */
    public int $maxExceptions = 1;

    /**
     * The number of seconds the job can run before timing out
     */
    public int $timeout = 10;

    public ?int $backoff = null;

    public function __construct(public readonly array $payload, ?array $queue = null)
    {
        if ($queue !== null) {
            $this->setQueueOptions($queue);
        } else {
            $this->setQueueOptions($this->payload['headers'][Header::QUEUE] ?? []);
        }
    }

    public function handle(Container $container): void
    {
        $container[$this->payload['headers'][Header::REPORTER_ID]]->relay($this->payload);
    }

    /**
     * Internally used by laravel
     */
    public function queue(Queue $queue, self $messageJob): void
    {
        $queue->pushOn($this->queue, $messageJob);
    }

    /**
     * Display message name
     */
    public function displayName(): string
    {
        return $this->payload['headers'][Header::EVENT_TYPE];
    }

    private function setQueueOptions(array $queue): void
    {
        $this->connection ??= $queue['connection'];
        $this->queue ??= $queue['name'];
        $this->tries ??= $queue['tries'];
        $this->delay ??= $queue['delay'];
        $this->maxExceptions ??= $queue['max_exceptions'];
        $this->timeout ??= $queue['timeout'];
        $this->backoff ??= $queue['backoff'];
    }
}
