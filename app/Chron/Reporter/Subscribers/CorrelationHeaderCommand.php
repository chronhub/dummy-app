<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Closure;
use Storm\Contract\Message\EventHeader;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

#[AsReporterSubscriber(
    supports: ['reporter.command.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 97000,
    autowire: true,
)]
class CorrelationHeaderCommand
{
    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($message->hasNot(EventHeader::EVENT_CAUSATION_ID) && $message->hasNot(EventHeader::EVENT_CAUSATION_TYPE)) {
                $message = $message
                    ->withHeader(EventHeader::EVENT_CAUSATION_ID, $message->header(Header::EVENT_ID))
                    ->withHeader(EventHeader::EVENT_CAUSATION_TYPE, $message->name());
            }

            $story->withMessage($message);
        };
    }
}
