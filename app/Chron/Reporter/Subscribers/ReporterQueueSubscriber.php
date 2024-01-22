<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use InvalidArgumentException;
use Storm\Contract\Tracker\MessageStory;

final readonly class ReporterQueueSubscriber
{
    const REPORTER_QUEUE = '__reporter_queue';

    public function __construct(private array $queue)
    {
        if ($queue === []) {
            throw new InvalidArgumentException('Reporter queue cannot be empty');
        }
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {

            $message = $story->message();

            if ($message->has(self::REPORTER_QUEUE)) {
                return;
            }

            $message = $message->withHeader(self::REPORTER_QUEUE, $this->queue);

            $story->withMessage($message);
        };
    }
}
