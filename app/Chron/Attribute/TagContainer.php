<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Reporter\QueueOption;
use App\Chron\Reporter\Subscribers\MessageQueueSubscriber;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use ReflectionClass;
use Storm\Contract\Reporter\Reporter;
use Storm\Tracker\GenericListener;

use function array_merge;
use function is_array;
use function is_string;
use function sprintf;

/**
 * @template T of array{reporter_id: string, tag_id: string, handler_id: string, handler_class: string, handler_method: string, priority: int}
 */
class TagContainer
{
    public const HANDLER_TAG_PREFIX = '#';

    public array $messages = [];

    public array $queueSubscribers = [];

    protected const TAG = 'message.handler.%s';

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
            $messageId = $this->tagConcrete($messageName, $priority);

            $this->container->bind($messageId, fn (): callable => $this->newHandlerInstance($data->reflectionClass, $data->handlerMethod));

            $queueOptions = $this->determineQueue($data->queue);

            $this->addQueueSubscriber($data->reporterId, $messageName, $queueOptions);

            $this->updateMessages($messageName, $messageId, $priority, $data->reporterId, $data->reflectionClass, $data->handlerMethod, $queueOptions);
        }
    }

    protected function updateMessages(
        string $messageName,
        string $messageId,
        int $key,
        string $reporterId,
        ReflectionClass $reflectionClass,
        string $handlerMethod,
        ?object $queue
    ): void {
        $this->messages[$messageName] = array_merge($this->messages[$messageName] ?? [], [$messageId]);

        $mapEntry = [
            'reporter_id' => $reporterId,
            'message_id' => $this->tagConcrete($messageName),
            'handler_id' => $messageId,
            'handler_class' => $reflectionClass->getName(),
            'handler_method' => $handlerMethod,
            'priority' => $key,
            'queue' => $queue,
        ];

        $this->map[$messageName] = array_merge($this->map[$messageName] ?? [], [$mapEntry]);
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        $concreteTag = sprintf(self::TAG, Str::remove('\\', Str::snake($concrete)));

        if ($key !== null) {
            return sprintf('%s%s', $concreteTag, self::HANDLER_TAG_PREFIX.$key);
        }

        return $concreteTag;
    }

    protected function newHandlerInstance(ReflectionClass $messageHandler, ?string $method): callable
    {
        $references = $this->referenceBuilder->fromConstructor($messageHandler);

        $instance = $this->container->make($messageHandler->getName(), ...$references);

        return ($method === null || $method === '__invoke') ? $instance : $instance->{$method}(...);
    }

    protected function addQueueSubscriber(string $reporterId, string $messageName, ?object $queue): void
    {
        if ($queue === null) {
            return;
        }

        if (isset($this->queueSubscribers[$messageName])) {
            $this->queueSubscribers[$messageName][] = $queue;

            return;
        }

        $this->queueSubscribers[$messageName] = [$queue];
    }

    protected function determineQueue(null|string|array $queue): ?object
    {
        return match (true) {
            is_string($queue) => $this->container[$queue],
            is_array($queue) => new QueueOption(...$queue),
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
