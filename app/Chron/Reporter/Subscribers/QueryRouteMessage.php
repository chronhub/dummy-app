<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use App\Chron\Reporter\Router\Routable;
use Closure;
use RuntimeException;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

#[AsReporterSubscriber(
    supports: ['reporter.query.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 20000,
    autowire: true,
)]
final readonly class QueryRouteMessage
{
    public function __construct(private Routable $router)
    {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($message->header(Header::EVENT_DISPATCHED)) {
                throw new RuntimeException("Message {$message->name()} already dispatched");
            }

            $messageHandlers = $this->router->route(
                $message->header(Header::REPORTER_ID),
                $message->name()
            );

            $message = $message->withHeader(Header::EVENT_DISPATCHED, true);

            $story->withHandlers($messageHandlers);

            $story->withMessage($message);
        };
    }
}
