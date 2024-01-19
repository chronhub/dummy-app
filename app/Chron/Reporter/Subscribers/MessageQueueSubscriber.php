<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\TagContainer;
use Closure;
use Illuminate\Support\Arr;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;

final readonly class MessageQueueSubscriber
{
    private array $queues;

    public function __construct(TagContainer $tagContainer)
    {
        $this->queues = $tagContainer->queueSubscribers;
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            $queue = $this->normalizeQueue($message->name());

            if ($queue === null) {
                logger('No queue found for message subscriber '.$message->name());

                return;
            }

            logger('Found queue for message subscriber '.$message->name());

            $message = $message->withHeader(Header::QUEUE, $queue->jsonSerialize());

            $story->withMessage($message);
        };
    }

    private function normalizeQueue(string $messageName): ?object
    {
        if (! isset($this->queues[$messageName])) {
            return null;
        }

        return Arr::first($this->queues[$messageName]);
    }
}
