<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Messaging\MessageHandler;
use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use App\Chron\Reporter\Producer\IlluminateQueue;
use App\Chron\Reporter\Router\Routable;
use Closure;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;

#[AsReporterSubscriber(
    supports: ['reporter.command.*', 'reporter.event.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 20000,
    autowire: true,
)]
final readonly class RouteMessage
{
    public function __construct(
        private Routable $router,
        private IlluminateQueue $queueDispatcher,
    ) {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            $chainHandler = $this->resolveQueue($message);

            $message = $message
                ->withHeader(Header::QUEUE, $chainHandler->getQueues())
                ->withHeader(Header::EVENT_DISPATCHED, true);

            $story->withHandlers($chainHandler->getSyncHandlers());

            $this->dispatchToQueue($message, $chainHandler->getAsyncHandler());

            $story->withMessage($message);
        };
    }

    private function resolveQueue(Message $message): ChainHandlerResolver
    {
        $messageHandlers = collect($this->router->route(
            $message->header(Header::REPORTER_ID),
            $message->name()
        ));

        $alreadyDispatched = $message->header(Header::EVENT_DISPATCHED);
        $queue = $alreadyDispatched ? $message->header(Header::QUEUE) : [];

        return (new ChainHandlerResolver($messageHandlers, $queue))->handle($alreadyDispatched);
    }

    private function dispatchToQueue(Message $message, ?MessageHandler $messageHandler): void
    {
        if ($messageHandler) {
            $this->queueDispatcher->toQueue($message, $messageHandler->queue());
        }
    }
}
