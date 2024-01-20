<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\MessageHandlerEntry;
use App\Chron\Reporter\QueueOption;
use App\Chron\Reporter\Subscribers\MessageQueueSubscriber;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use RuntimeException;
use Storm\Contract\Reporter\Reporter;
use Storm\Tracker\GenericListener;

use function array_merge;
use function func_get_args;
use function is_array;
use function is_string;
use function sprintf;

/**
 * @template T of MessageHandlerEntry
 */
class TagContainer
{
    public const HANDLER_TAG_PREFIX = '#';

    protected const TAG = 'message.handler.%s';

    public array $messages = [];

    public array $queueSubscribers = [];

    /**
     * @var array<T>|array
     */
    public array $map = [];

    public function __construct(
        protected SimpleLoader $simpleLoader,
        protected ReferenceBuilder $referenceBuilder,
        protected Container $container
    ) {
    }

    public function find(string $messageName): iterable
    {
        $tagName = $this->tagConcrete($messageName);

        return $this->container->tagged($tagName);
    }

    public function autoTag(): void
    {
        $this->simpleLoader->load();

        foreach ($this->simpleLoader->messages as $messageName => $messageHandler) {
            $this->processMessageHandlers($messageName, $messageHandler);
        }

        foreach ($this->messages as $messageName => $messageHandlers) {
            $this->container->tag($messageHandlers, $this->tagConcrete($messageName));
        }
    }

    protected function processMessageHandlers(string $messageName, array $messageHandlers): void
    {
        /** @var MessageHandlerData $data */
        foreach ($messageHandlers as $priority => $data) {
            $messageHandlerId = $this->tagConcrete($messageName, $priority);

            $this->container->bind($messageHandlerId, fn (): callable => $this->newHandlerInstance($data));

            $queueOptions = $this->determineQueue($data->queue); // do not resolve queue here

            $this->addQueueSubscriber($messageName, $priority, $queueOptions);

            $this->updateMessages($messageName, $messageHandlerId, $data, $queueOptions);
        }
    }

    protected function updateMessages(
        string $messageName,
        string $messageHandlerId,
        MessageHandlerData $data,
        ?object $queue
    ): void {
        $this->messages[$messageName] = array_merge($this->messages[$messageName] ?? [], [$messageHandlerId]);

        $entry = new MessageHandlerEntry($this->tagConcrete($messageName), ...func_get_args());

        $this->map[$messageName] = array_merge($this->map[$messageName] ?? [], [$entry]);
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        $concreteTag = sprintf(self::TAG, Str::remove('\\', Str::snake($concrete)));

        if ($key !== null) {
            return sprintf('%s%s', $concreteTag, self::HANDLER_TAG_PREFIX.$key);
        }

        return $concreteTag;
    }

    protected function newHandlerInstance(MessageHandlerData $data): callable
    {
        $references = $this->referenceBuilder->fromConstructor($data->reflectionClass);

        $instance = $this->container->make($data->reflectionClass->getName(), ...$references);

        $callback = ($data->handlerMethod === '__invoke') ? $instance : $instance->{$data->handlerMethod}(...);

        return new MessageHandler($callback, $data->priority);
    }

    protected function addQueueSubscriber(string $messageName, int $priority, ?object $queue): void
    {
        if (isset($this->queueSubscribers[$messageName])) {
            if (! $queue) {
                $this->queueSubscribers[$messageName] += [$priority => $queue];

                return;
            }

            foreach ($this->queueSubscribers[$messageName] as $_queue) {
                if ($_queue === null) {
                    continue;
                }

                if ($_queue->jsonSerialize() !== $queue->jsonSerialize()) {
                    throw new RuntimeException('Cannot add multiple queue subscribers for the same message');
                }
            }

            $this->queueSubscribers[$messageName] += [$priority => $queue];

            return;
        }

        $this->queueSubscribers[$messageName] = [$priority => $queue];
    }

    protected function determineQueue(null|string|array $queue): ?object
    {
        return match (true) {
            is_string($queue) => $this->container[$queue],
            is_array($queue) => new QueueOption(...$queue), // todo queue option from config
            default => null,
        };
    }

    protected function resolveQueueSubscriber(): void
    {
        foreach ($this->queueSubscribers as $reporterId => $queue) {
            $this->container->resolving($reporterId, function (Reporter $reporter) use ($queue) {
                $subscriber = new GenericListener(
                    Reporter::DISPATCH_EVENT,
                    new MessageQueueSubscriber($queue),
                    20001 // todo before route
                );

                $reporter->subscribe($subscriber);

                return $reporter;
            });
        }
    }
}
