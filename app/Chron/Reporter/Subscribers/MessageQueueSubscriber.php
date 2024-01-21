<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\TagContainer;
use Closure;
use Illuminate\Support\Arr;
use RuntimeException;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;

use function is_object;

final readonly class MessageQueueSubscriber
{
    private array $queues;

    public function __construct(TagContainer $tagContainer)
    {
        $this->queues = $tagContainer->getQueues();
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {

            return;
            $message = $story->message();

            $reporterQueue = $message->header(Header::QUEUE);

            $queue = $this->normalizeQueue($message, $reporterQueue);

            if ($queue === null) {
                return;
            }

            $message = $message->withHeader(Header::QUEUE, $queue);

            $story->withMessage($message);
        };
    }

    private function normalizeQueue(Message $message, null|array|object $reporterQueue): ?array
    {
        if (! isset($this->queues[$message->name()])) {
            return null;
        }

        if ($message->header(Header::EVENT_DISPATCHED) === true) {
            return $message->header(Header::QUEUE);
        }

        if (is_object($reporterQueue)) {
            $reporterQueue = $reporterQueue->jsonSerialize();
        }

        $_queues = $this->queues[$message->name()];
        foreach ($_queues as $priority => &$queue) {
            if ($queue === null) {
                $queue = $reporterQueue;
            }

            if (is_object($queue)) {
                $queue = $queue->jsonSerialize();
            }

            if ($queue !== null && $reporterQueue !== null && $queue !== $reporterQueue) {
                throw new RuntimeException('Cannot add multiple queue handler for the same message');
            }
        }

        return Arr::first($_queues);
    }
}
