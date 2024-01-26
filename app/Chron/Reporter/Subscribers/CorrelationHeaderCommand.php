<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Storm\Contract\Message\EventHeader;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;

class CorrelationHeaderCommand
{
    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            $message = $message
                ->withHeader(EventHeader::EVENT_CAUSATION_ID, $message->header(Header::EVENT_ID))
                ->withHeader(EventHeader::EVENT_CAUSATION_TYPE, $message->name());

            $story->withMessage($message);
        };
    }
}
