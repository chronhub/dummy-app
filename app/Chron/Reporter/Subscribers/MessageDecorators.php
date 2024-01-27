<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Carbon\Carbon;
use Closure;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;
use Symfony\Component\Uid\Uuid;

#[AsReporterSubscriber(
    supports: ['*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 99000,
    autowire: true,
)]
final class MessageDecorators
{
    // todo implement chain decorator message with reference
    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($message->hasNot(Header::EVENT_ID)) {
                $message = $message->withHeader(Header::EVENT_ID, Uuid::v4()->jsonSerialize());
            }

            if ($message->hasNot(Header::EVENT_TIME)) {
                $message = $message->withHeader(Header::EVENT_TIME, Carbon::now('UTC')->format('Y-m-d\TH:i:s.u'));
            }

            if ($message->hasNot(Header::EVENT_TYPE)) {
                $message = $message->withHeader(Header::EVENT_TYPE, $message->name());
            }

            if ($message->hasNot(Header::EVENT_DISPATCHED)) {
                $message = $message->withHeader(Header::EVENT_DISPATCHED, false);
            }

            $story->withMessage($message);
        };
    }
}
