<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\MessageHandler;
use RuntimeException;

use function array_keys;
use function array_merge;
use function sprintf;

class DispatchHandlerStrategy
{
    protected array $storyHandlers = [];

    protected ?MessageHandler $handlerToDispatch = null;

    public function __construct(
        private readonly array $messageHandlers,
        private array $queues,
        private readonly ?array $reporterQueue,
    ) {
    }

    public function handle(bool $alreadyDispatched): void
    {
        if ($alreadyDispatched) {
            $this->validateCurrentQueue();
            $this->handleFirstToHandle();
        } else {
            $this->formatQueue();
        }

        $this->handleChainSyncHandlers();
        $this->handleNextAsyncHandler();
    }

    /**
     * @return array<MessageHandler>|array
     */
    public function getStoryHandlers(): array
    {
        return $this->storyHandlers;
    }

    public function getAsyncHandler(): ?MessageHandler
    {
        return $this->handlerToDispatch;
    }

    public function getQueues(): array
    {
        return $this->queues;
    }

    protected function handleChainSyncHandlers(): void
    {
        $applyHandlers = $this->filterSyncHandlers($this->messageHandlers);

        foreach ($applyHandlers as $applyHandler) {
            $this->markQueueHandled($applyHandler);

            $this->markQueueDispatched($applyHandler);
        }

        $this->storyHandlers = array_merge($this->storyHandlers, $applyHandlers);
    }

    protected function handleNextAsyncHandler(): void
    {
        $nextHandler = $this->findNextAsyncHandler();

        if ($nextHandler) {
            $this->markQueueDispatched($nextHandler);

            $this->setHandlerToDispatch($nextHandler);
        }
    }

    protected function handleFirstToHandle(): void
    {
        foreach ($this->queues as $priority => $queue) {
            if ($queue['dispatched'] === true && $queue['handled'] === false) {
                $messageHandler = $this->getMessageHandlerByPriority($this->messageHandlers, $priority);

                if ($this->storyHandlers !== []) {
                    throw new RuntimeException('Story handlers should be empty when handle first handler');
                }

                $this->storyHandlers = [$messageHandler];

                $this->markQueueHandled($messageHandler);

                break;
            }
        }
    }

    protected function markQueueHandled(MessageHandler $messageHandler): void
    {
        $this->queues[$messageHandler->priority()]['handled'] = true;
    }

    protected function markQueueDispatched(MessageHandler $messageHandler): void
    {
        $this->queues[$messageHandler->priority()]['dispatched'] = true;
    }

    protected function filterSyncHandlers(array $messageHandlers): array
    {
        // the reporter queue is not null, so we don't dispatch any sync handler
        if ($this->reporterQueue !== null) {
            return [];
        }

        $chainSyncHandlers = [];

        foreach ($this->queues as $priority => $queue) {
            $messageHandler = $this->getMessageHandlerByPriority($messageHandlers, $priority);

            if ($queue['handled'] === true) {
                continue;
            }

            if ($messageHandler->queue() === null && $queue['dispatched'] === false) {
                $chainSyncHandlers[] = $messageHandler;
            }

            if ($messageHandler->queue() !== null) {
                break;
            }
        }

        return $chainSyncHandlers;
    }

    protected function findNextAsyncHandler(): ?MessageHandler
    {
        foreach ($this->queues as $priority => $queue) {
            $messageHandler = $this->getMessageHandlerByPriority($this->messageHandlers, $priority);

            if ($queue['dispatched'] === false && ($messageHandler->queue() !== null || $this->reporterQueue !== null)) {
                return $messageHandler;
            }
        }

        return null;
    }

    protected function getMessageHandlerByPriority(array $messageHandlers, int $queuePriority): MessageHandler
    {
        foreach ($messageHandlers as $messageHandler) {
            if ($messageHandler->priority() === $queuePriority) {
                return $messageHandler;
            }
        }

        throw new RuntimeException(sprintf('A message handler should be found at priority %d but none found', $queuePriority));
    }

    protected function formatQueue(): void
    {
        $queues = [];

        foreach ($this->messageHandlers as $handler) {
            $queues[$handler->priority()] = [
                'name' => $handler->name(),
                'queue' => $handler->queue(),
                'handled' => false,
                'dispatched' => false,
            ];
        }

        $this->queues = $queues;
    }

    protected function validateCurrentQueue(): void
    {
        $previousPriority = null;

        foreach ($this->queues as $priority => $queue) {
            if (array_keys($queue) !== ['name', 'queue', 'handled', 'dispatched']) {
                throw new RuntimeException('Invalid queue format');
            }

            if ($previousPriority !== null && $priority <= $previousPriority) {
                throw new RuntimeException('Invalid queue priority sequence');
            }

            $previousPriority = $priority;
        }
    }

    protected function setHandlerToDispatch(MessageHandler $messageHandler): void
    {
        if ($this->handlerToDispatch !== null) {
            throw new RuntimeException(' Handler to dispatch already set');
        }

        $this->handlerToDispatch = $messageHandler;
    }
}
