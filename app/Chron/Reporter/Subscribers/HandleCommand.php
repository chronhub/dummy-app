<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Closure;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

#[AsReporterSubscriber(
    supports: ['reporter.command.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 0,
    autowire: true,
)]
final class HandleCommand
{
    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $messageHandler = $story->handlers()->current();

            if ($messageHandler) {
                $messageHandler($story->message()->event());
            }

            if ($messageHandler !== null || $story->message()->header(Header::EVENT_DISPATCHED) === true) {
                $story->markHandled(true);
            }
        };
    }
}
