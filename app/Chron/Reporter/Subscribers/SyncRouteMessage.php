<?php

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Storm\Contract\Message\MessageProducer;
use Storm\Contract\Tracker\MessageStory;
use Storm\Reporter\Routing;

final readonly class SyncRouteMessage
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

            $dispatchedMessage = ($this->messageProducer)($message);

            $story->withMessage($dispatchedMessage);

            $eventName = $story->message()->name();

            $messageHandlers = $this->routing->route($eventName);

            $story->withHandlers($messageHandlers);
        };
    }
}
