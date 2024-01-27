<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Closure;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

#[AsReporterSubscriber(
    supports: ['reporter.event.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 0,
    autowire: true,
)]
final class HandleEvent
{
    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            foreach ($story->handlers() as $eventHandler) {
                $eventHandler($story->message()->event());
            }

            $story->markHandled(true);
        };
    }
}
