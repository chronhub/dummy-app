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

final readonly class RouteMessageSubscriber
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

            $queueResolver = $this->resolve($message);

            $message = $message
                ->withHeader(Header::QUEUE, $queueResolver->getQueues())
                ->withHeader(Header::EVENT_DISPATCHED, true);

            $story->withHandlers($queueResolver->getSyncHandlers());

            $this->dispatchToQueue($message, $queueResolver->getAsyncHandler());

            $story->withMessage($message);
        };
    }

    private function resolve(Message $message): ChainHandlerResolver
    {
        $messageHandlers = $this->routing->route($message->name());

        $alreadyDispatched = $message->header(Header::EVENT_DISPATCHED);

        $queue = $alreadyDispatched ? $message->header(Header::QUEUE) : [];

        $resolver = new ChainHandlerResolver($messageHandlers, $queue);

        return $resolver->handle($alreadyDispatched);
    }

    private function dispatchToQueue(Message $message, ?MessageHandler $messageHandler): void
    {
        if ($messageHandler) {
            $this->dispatcher->toQueue($message, $messageHandler->queue());
        }
    }
}
