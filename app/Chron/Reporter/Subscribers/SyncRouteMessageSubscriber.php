<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use RuntimeException;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;
use Storm\Reporter\Routing;

final readonly class SyncRouteMessageSubscriber
{
    public function __construct(private Routing $routing)
    {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($message->header(Header::EVENT_DISPATCHED)) {
                throw new RuntimeException("Message {$message->name()} already dispatched");
            }

            $messageHandlers = $this->routing->route($message->name());

            $message = $message->withHeader(Header::EVENT_DISPATCHED, true);

            $story->withHandlers($messageHandlers);

            $story->withMessage($message);
        };
    }
}
