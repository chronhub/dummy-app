<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Storm\Contract\Message\Header;
use Storm\Contract\Message\MessageProducer;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;
use Storm\Reporter\Routing;

final readonly class RouteMessageSubscriber
{
    public function __construct(
        private Routing $routing,
        private MessageProducer $messageProducer
    ) {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($this->shouldBeQueue($message)) {
                $dispatchedMessage = ($this->messageProducer)($message);

                $story->withMessage($dispatchedMessage);

                return;
            }

            logger('HANDLE SYNC MESSAGE '.$message->name());

            $messageHandlers = $this->routing->route($message->name());

            $story->withHandlers($messageHandlers);
        };
    }

    protected function shouldBeQueue(Message $message): bool
    {
        // queue can be overridden per reporter
        if ($message->header(Header::QUEUE) === null) {
            return false;
        }

        return $message->header(Header::EVENT_DISPATCHED) !== true;
    }
}
