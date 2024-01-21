<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\MessageHandler;
use App\Chron\Reporter\IlluminateQueue;
use Closure;
use RuntimeException;
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
        /**
         * handle case where
         *      - when sync sequence, we handle them all, if the next handler is async, we dispatch it
         *      - when async sequence, we dispatch the handler one by one
         */
        return function (MessageStory $story): void {
            $message = $story->message();

            $messageHandlers = $this->routing->route($message->name());

            if ($message->header(Header::EVENT_DISPATCHED) === true) {
                $currentQueue = $this->validateCurrentQueue($message->header(Header::QUEUE));

                // - get the first which has been dispatched but not handled
                $messageHandler = $this->getFirstHandlerToHandle($currentQueue, $messageHandlers);

                if ($messageHandler !== null) {

                    $syncMessage = $this->handleSync($story, $message, $messageHandler, $currentQueue);

                    // todo check if next handlers is sync

                } else {
                    // -- find next which need to be dispatched
                    $messageHandler = $this->getNextHandlerToDispatch($currentQueue, $messageHandlers);

                    if ($messageHandler !== false) {
                        // -- no queue, handle sync, and check if we need to dispatch the next handler
                        if ($messageHandler->queue() === null) {

                            $syncMessage = $this->handleSync($story, $message, $messageHandler, $currentQueue);

                            $currentQueue = $syncMessage->header(Header::QUEUE);

                            $nextHandler = $this->hasNextAsyncQueueHandler($currentQueue, $messageHandlers);

                            if ($nextHandler !== null) {
                                $this->handleAsync($story, $syncMessage, $nextHandler, $currentQueue);
                            }

                            return;
                        }

                        $markDispatched = $this->markQueueDispatched($currentQueue, $messageHandler);

                        $message = $message->withHeader(Header::QUEUE, $markDispatched);

                        $this->dispatchToQueue($message, $currentQueue[$messageHandler->priority()]['queue']);
                    } else {
                        // if another strategy dispatches all async handlers once and all sync handlers once,
                        // it should fall here.
                        // we could dispatch an event to make the system aware of the full completion of the message
                        // a message handler can hold the completion message to dispatch,
                        // known from attribute

                        // -- no more handlers to dispatch, should not happen
                        throw new RuntimeException('No more handlers to dispatch');
                    }
                }
            } else {

                // todo handle case when dev set queue
                // - format queue
                $currentQueue = $this->formatQueueHandler($messageHandlers);

                $message = $message->withHeader(Header::QUEUE, $currentQueue);
                $message = $message->withHeader(Header::EVENT_DISPATCHED, true);

                $syncMessageHandlers = $this->filterSyncNewMessage($currentQueue, $messageHandlers);

                $wasSync = false;
                if ($syncMessageHandlers !== []) {
                    $this->handleManySync($story, $message, $syncMessageHandlers, $currentQueue);
                    $wasSync = true;
                }

                // retrieve a message if it has been handled sync
                $message = $wasSync ? $story->message() : $message;

                $nextQueue = $message->header(Header::QUEUE) ?? $currentQueue;

                $nextHandler = $this->hasNextAsyncQueueHandler($nextQueue, $messageHandlers);

                if ($nextHandler !== null) {
                    $this->handleAsync($story, $message, $nextHandler, $nextQueue);
                }
            }
        };
    }

    protected function dispatchToQueue(Message $message, ?array $handlerQueue): void
    {
        $this->dispatcher->toQueue($message, $handlerQueue);
    }

    /**
     * @param array<MessageHandler> $handlers
     */
    protected function formatQueueHandler(array $handlers): array
    {
        $queues = [];
        foreach ($handlers as $handler) {
            $queues[$handler->priority()] = [
                'class' => $handler->handlerClass(),
                'queue' => $handler->queue(),
                'handled' => false,
                'dispatched' => false,
            ];
        }

        return $queues;
    }

    /**
     * Filter sync message which has not been dispatched and handled yet
     * Stop when a message is async, queue is not null
     */
    protected function filterSyncNewMessage(array $currentQueue, array $messageHandlers): array
    {
        $syncSequence = [];

        foreach ($currentQueue as $priority => $queue) {
            $messageHandler = $this->getMessageHandlerByQueuePriority($messageHandlers, $priority);

            if ($messageHandler->queue() !== null) {
                break;
            }

            if ($queue['handled'] === true) {
                continue; // raise exception?
            }

            if ($queue['dispatched'] === false) {
                $syncSequence[] = $messageHandler;
            }
        }

        return $syncSequence;
    }

    private function getMessageHandlerByQueuePriority(array $messageHandlers, int $queuePriority): MessageHandler
    {
        foreach ($messageHandlers as $messageHandler) {
            if ($messageHandler->priority() === $queuePriority) {
                return $messageHandler;
            }
        }

        throw new RuntimeException('a MessageHandler should be found at priority '.$queuePriority.' but none found');
    }

    /**
     * Get the first handler which has been dispatched but not handled yet.
     * The main condition is Header::QUEUE['__dispatched'] === true
     */
    protected function getFirstHandlerToHandle(array $currentQueue, array $messageHandlers): ?MessageHandler
    {
        foreach ($currentQueue as $priority => $queue) {
            if ($queue['dispatched'] === true && $queue['handled'] === false) {
                return $this->getMessageHandlerByQueuePriority($messageHandlers, $priority);
            }
        }

        return null;
    }

    /**
     * Get the next handler which has not been dispatched yet.
     *      - Sync or async
     *      -Header::QUEUE['__dispatched'] === true
     */
    protected function getNextHandlerToDispatch(array $currentQueue, array $messageHandlers): false|MessageHandler
    {
        foreach ($currentQueue as $priority => $queue) {
            if ($queue['dispatched'] === false) {
                return $this->getMessageHandlerByQueuePriority($messageHandlers, $priority);
            }
        }

        return false;
    }

    /**
     * Get the next handler which has not been dispatched yet.
     * Header::QUEUE['__dispatched'] === true or false
     */
    protected function hasNextAsyncQueueHandler(array $currentQueue, array $messageHandlers): ?MessageHandler
    {
        foreach ($currentQueue as $priority => $queue) {
            $messageHandler = $this->getMessageHandlerByQueuePriority($messageHandlers, $priority);

            if ($queue['dispatched'] === false && $messageHandler->queue() !== null) {
                return $messageHandler;
            }
        }

        return null;
    }

    protected function validateCurrentQueue(array $currentQueue): array
    {
        foreach ($currentQueue as $queue) {
            if (! isset($queue['class'], $queue['queue'], $queue['handled'], $queue['dispatched'])) {
                throw new RuntimeException('Invalid queue format');
            }
        }

        return $currentQueue;
    }

    private function markQueueHandled(MessageHandler $messageHandler, array $currentQueue): array
    {
        $currentQueue[$messageHandler->priority()]['handled'] = true;

        return $currentQueue;
    }

    private function markQueueDispatched(array $currentQueue, MessageHandler $messageHandler): array
    {
        $currentQueue[$messageHandler->priority()]['dispatched'] = true;

        return $currentQueue;
    }

    private function handleSync(MessageStory $story, Message $message, MessageHandler $messageHandler, array $currentQueue): Message
    {
        $markHandled = $this->markQueueHandled($messageHandler, $currentQueue);

        $story->withHandlers([$messageHandler]);

        $story->withMessage($message->withHeader(Header::QUEUE, $markHandled));

        return $message;
    }

    private function handleManySync(MessageStory $story, Message $message, array $messageHandlers, array $currentQueue): void
    {
        foreach ($messageHandlers as $messageHandler) {
            logger()->debug('handle sync', ['message' => $message->name(), 'priority' => $messageHandler->priority()]);
            $currentQueue = $this->markQueueHandled($messageHandler, $currentQueue);
        }

        $story->withHandlers($messageHandlers);

        $message = $message->withHeader(Header::QUEUE, $currentQueue);

        $story->withMessage($message);
    }

    private function handleAsync(MessageStory $story, Message $message, MessageHandler $messageHandler, array $currentQueue): void
    {
        $markDispatched = $this->markQueueDispatched($currentQueue, $messageHandler);

        $messageDispatched = $message->withHeader(Header::QUEUE, $markDispatched);

        $this->dispatchToQueue($messageDispatched, $messageHandler->queue());

        $story->withMessage($messageDispatched);
    }
}