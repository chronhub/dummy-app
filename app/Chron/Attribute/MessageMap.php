<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use App\Chron\Attribute\MessageHandler\MessageHandlerEntry;
use App\Chron\Reporter\DomainType;
use App\Chron\Reporter\QueueOption;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function array_merge;
use function func_get_args;
use function is_array;
use function is_string;
use function uksort;

class MessageMap
{
    /**
     * @var Collection<string, array<MessageHandlerData>>
     */
    protected Collection $map;

    /**
     * @var array array<string, array<string>>
     */
    protected array $bindings;

    /**
     * @var array array<string, array<MessageHandlerEntry>>
     */
    protected array $entries;

    /**
     * @var array array<string, array<int, object|null>>
     */
    protected array $queues = [];

    protected Closure $prefixResolver;

    public function __construct(
        protected MessageLoader $messageLoader,
        protected ReferenceBuilder $referenceBuilder,
        protected Container $container
    ) {
        $this->map = new Collection();
    }

    public function load(): void
    {
        $this->messageLoader->getMessages()->each(fn (array $data) => $this->updateMap(...$data));

        $this->map->each(fn (array $messageHandlers, string $messageName) => $this->bind($messageName, $messageHandlers));
    }

    public function getBindings(): Collection
    {
        return collect($this->bindings);
    }

    public function getData(): Collection
    {
        return $this->map;
    }

    public function getEntries(): Collection
    {
        return collect($this->entries);
    }

    public function getQueues(): array
    {
        return $this->queues;
    }

    public function setPrefixResolver(Closure $prefixResolver): void
    {
        $this->prefixResolver = $prefixResolver;
    }

    protected function updateMap(
        ReflectionClass $reflectionClass,
        ?ReflectionMethod $reflectionMethod,
        AsCommandHandler|AsEventHandler|AsQueryHandler $attribute
    ): void {
        $handlerMethod = $this->determineHandlerMethod($attribute->method, $reflectionMethod);

        // todo remove reflection in data and entry
        $data = new MessageHandlerData($reflectionClass, $attribute, $handlerMethod);

        if (! $this->map->has($data->handles)) {
            $this->map->put($data->handles, [$data->priority => $data]);
        } else {
            $this->assertCountHandlerPerType($data);

            $messageHandlers = $this->map->get($data->handles);

            if (isset($messageHandlers[$data->priority])) {
                throw new RuntimeException("Duplicate priority $data->priority for $data->handles");
            }

            $messageHandlers[$data->priority] = $data;

            uksort($messageHandlers, fn (int $a, int $b): int => $a <=> $b);

            $this->map->put($data->handles, $messageHandlers);
        }
    }

    protected function bind(string $messageName, array $messageHandlers): void
    {
        foreach ($messageHandlers as $priority => $data) {
            $messageHandlerId = $this->tagConcrete($messageName, $priority);

            $this->container->bind($messageHandlerId, fn (): callable => $this->newHandlerInstance($data));

            $queueOptions = $this->determineQueue($data->queue); // do not resolve queue here

            $this->addQueueSubscriber($messageName, $priority, $queueOptions);

            $this->updateBinding($messageName, $messageHandlerId, $data, $queueOptions);
        }
    }

    protected function updateBinding(string $messageName, string $messageHandlerId, MessageHandlerData $data, ?object $queue): void
    {
        $this->bindings[$messageName] = array_merge($this->bindings[$messageName] ?? [], [$messageHandlerId]);

        $entry = new MessageHandlerEntry($this->tagConcrete($messageName), ...func_get_args());

        $this->entries[$messageName] = array_merge($this->entries[$messageName] ?? [], [$entry]);
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        return ($this->prefixResolver)($concrete, $key);
    }

    protected function newHandlerInstance(MessageHandlerData $data): callable
    {
        // todo references must be done in messageLoader
        $references = $this->referenceBuilder->fromConstructor($data->reflectionClass);

        $instance = $this->container->make($data->reflectionClass->getName(), ...$references);

        $callback = ($data->handlerMethod === '__invoke') ? $instance : $instance->{$data->handlerMethod}(...);

        return new MessageHandler($callback, $data->priority);
    }

    protected function addQueueSubscriber(string $messageName, int $priority, ?object $queue): void
    {
        // todo queue should not be resolved as is will be serialized anyway
        //  but we still need to known default queue if is array to merge with queue option
        if (isset($this->queues[$messageName])) {
            if (! $queue) {
                $this->queues[$messageName] += [$priority => $queue];

                return;
            }

            foreach ($this->queues[$messageName] as $_queue) {
                if ($_queue === null) {
                    continue;
                }

                if ($_queue->jsonSerialize() !== $queue->jsonSerialize()) {
                    throw new RuntimeException('Cannot add multiple queue subscribers for the same message');
                }
            }

            $this->queues[$messageName] += [$priority => $queue];

            return;
        }

        $this->queues[$messageName] = [$priority => $queue];
    }

    protected function determineQueue(null|string|array $queue): ?object
    {
        return match (true) {
            is_string($queue) => $this->container[$queue],
            is_array($queue) => new QueueOption(...$queue), // todo queue option from config
            default => null,
        };
    }

    protected function determineHandlerMethod(?string $handlerMethod, ?ReflectionMethod $reflectionMethod): string
    {
        return match (true) {
            $handlerMethod !== null => $handlerMethod,
            $reflectionMethod !== null => $reflectionMethod->getName(),
            default => '__invoke',
        };
    }

    private function assertCountHandlerPerType(MessageHandlerData $data): void
    {
        if ($data->type === DomainType::EVENT) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query is allowed');
    }
}
