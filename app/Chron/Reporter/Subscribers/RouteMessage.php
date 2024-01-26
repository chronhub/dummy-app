<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\MessageHandler\MessageHandler;
use App\Chron\Reporter\Producer\IlluminateQueue;
use Closure;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;
use Storm\Reporter\Routing;

final readonly class RouteMessage
{
    public function __construct(
        private Routing $routing,
        private IlluminateQueue $dispatcher,
    ) {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            $queueResolver = $this->resolveQueue($message);

            $message = $message
                ->withHeader(Header::QUEUE, $queueResolver->getQueues())
                ->withHeader(Header::EVENT_DISPATCHED, true);

            $story->withHandlers($queueResolver->getSyncHandlers());

            $this->dispatchToQueue($message, $queueResolver->getAsyncHandler());

            $story->withMessage($message);
        };
    }

    private function resolveQueue(Message $message): ChainHandlerResolver
    {
        // todo make collection of handlers from routing route
        $messageHandlers = collect($this->routing->route($message->name()));

        $alreadyDispatched = $message->header(Header::EVENT_DISPATCHED);
        $queue = $alreadyDispatched ? $message->header(Header::QUEUE) : [];

        return (new ChainHandlerResolver($messageHandlers, $queue))->handle($alreadyDispatched);
    }

    private function dispatchToQueue(Message $message, ?MessageHandler $messageHandler): void
    {
        if ($messageHandler) {
            $this->dispatcher->toQueue($message, $messageHandler->queue());
        }
    }
}
