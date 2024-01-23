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

            $strategy = $this->handle($message);

            $message = $message
                ->withHeader(Header::QUEUE, $strategy->getQueues())
                ->withHeader(Header::EVENT_DISPATCHED, true);

            $handlers = $strategy->getStoryHandlers();
            $story->withHandlers($handlers);

            $this->dispatchToQueue($message, $strategy->getAsyncHandler());

            $story->withMessage($message);
        };
    }

    private function handle(Message $message): DispatchHandlerStrategy
    {
        $messageHandlers = $this->routing->route($message->name());

        $alreadyDispatched = $message->header(Header::EVENT_DISPATCHED);

        $queues = $alreadyDispatched ? $message->header(Header::QUEUE) : [];

        $strategy = new DispatchHandlerStrategy($messageHandlers, $queues);

        $strategy->handle($alreadyDispatched);

        return $strategy;
    }

    private function dispatchToQueue(Message $message, ?MessageHandler $messageHandler): void
    {
        if ($messageHandler) {
            $queue = $messageHandler->queue();

            $this->dispatcher->toQueue($message, $queue);
        }
    }
}
