<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\MessageHandler;
use App\Chron\Reporter\IlluminateQueue;
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

            $reporterQueue = $this->getReporterQueue($message);

            $strategy = $this->handle($message, $reporterQueue);

            $queues = $strategy->getQueues();

            $message = $message
                ->withHeader(Header::QUEUE, $queues)
                ->withHeader(Header::EVENT_DISPATCHED, true);

            $handlers = $strategy->getStoryHandlers();
            $story->withHandlers($handlers);

            $this->dispatchToQueue($message, $strategy->getAsyncHandler(), $reporterQueue);

            $story->withMessage($message);
        };
    }

    private function handle(Message $message, ?array $reporterQueue): DispatchHandlerStrategy
    {
        $messageHandlers = $this->routing->route($message->name());

        $alreadyDispatched = $message->header(Header::EVENT_DISPATCHED);

        $queues = $alreadyDispatched ? $message->header(Header::QUEUE) : [];

        $strategy = new DispatchHandlerStrategy($messageHandlers, $queues, $reporterQueue);

        $strategy->handle($alreadyDispatched);

        return $strategy;
    }

    private function dispatchToQueue(Message $message, ?MessageHandler $messageHandler, ?array $reporterQueue): void
    {
        if ($messageHandler) {
            $queue = $messageHandler->queue() ?? $reporterQueue;

            $this->dispatcher->toQueue($message, $queue);
        }
    }

    private function getReporterQueue(Message $message): ?array
    {
        return $message->header(ReporterQueueSubscriber::REPORTER_QUEUE) ?? null;
    }
}
