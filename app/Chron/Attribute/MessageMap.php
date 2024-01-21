<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\MessageHandlerAttribute;
use App\Chron\Reporter\DomainType;
use App\Chron\Reporter\QueueOption;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use RuntimeException;

use function array_merge;
use function is_array;
use function is_string;
use function uksort;

class MessageMap
{
    // todo handler is dedicated to a specific reporter,
    //  when find message handler we need to check if reporter is the same

    /**
     * @var Collection<string, array<MessageHandlerAttribute>>
     */
    protected Collection $map;

    protected array $bindings;

    protected array $entries;

    protected array $queues = [];

    protected Closure $prefixResolver;

    public function __construct(
        protected MessageLoader $messageLoader,
        protected Application $app
    ) {
        $this->map = new Collection();
    }

    public function load(): void
    {
        $this->messageLoader->getAttributes()->each(fn (array $data) => $this->build(...$data));

        $this->map->each(
            fn (array $messageHandlers, string $messageName) => $this->bind($messageName, $messageHandlers)
        );
    }

    public function getBindings(): Collection
    {
        return collect($this->bindings);
    }

    public function getEntries(): Collection
    {
        return collect($this->entries);
    }

    public function getQueues(): array
    {
        return $this->queues;
    }

    public function getQueueForMessageName($messageName): ?array
    {
        return $this->queues[$messageName] ?? null;
    }

    public function setPrefixResolver(Closure $prefixResolver): void
    {
        $this->prefixResolver = $prefixResolver;
    }

    protected function build(MessageHandlerAttribute $attribute): void
    {
        if (! $this->map->has($attribute->handles)) {
            $this->map->put($attribute->handles, [$attribute->priority => $attribute]);
        } else {
            $this->assertCountHandlerPerType($attribute);

            $messageHandlers = $this->map->get($attribute->handles);

            if (isset($messageHandlers[$attribute->priority])) {
                throw new RuntimeException("Duplicate priority $attribute->priority for $attribute->handles");
            }

            $messageHandlers[$attribute->priority] = $attribute;

            uksort($messageHandlers, fn (int $a, int $b): int => $a <=> $b);

            $this->map->put($attribute->handles, $messageHandlers);
        }
    }

    protected function bind(string $messageName, array $messageHandlers): void
    {
        foreach ($messageHandlers as $priority => $attribute) {
            $messageHandlerId = $this->tagConcrete($messageName, $priority);

            $queue = $this->determineQueue($attribute->queue); // todo we do not check as is queue is valid across all handlers

            $this->app->bind($messageHandlerId, fn (): callable => $this->newHandlerInstance($attribute, $queue));

            //$this->addQueueSubscriber($messageName, $priority, $queue);

            $this->updateBinding($messageName, $messageHandlerId, $attribute, $priority, $queue);
        }
    }

    protected function updateBinding(string $messageName, string $messageHandlerId, MessageHandlerAttribute $attribute, $priority, ?array $queue): void
    {
        $this->bindings[$messageName] = array_merge($this->bindings[$messageName] ?? [], [$priority => $messageHandlerId]);

        $attribute = $attribute->newInstance($messageHandlerId, $this->tagConcrete($messageName), $queue);

        $this->entries[$messageName] = array_merge($this->entries[$messageName] ?? [], [$attribute]);
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        return ($this->prefixResolver)($concrete, $key);
    }

    protected function newHandlerInstance(MessageHandlerAttribute $attribute, ?array $queue): callable
    {
        $references = [];

        // assume constructor references, till we do not support other methods
        if ($attribute->references !== []) {
            foreach ($attribute->references as [$parameterName, $serviceId]) {
                $references[] = [$parameterName => $this->app[$serviceId]];
            }
        }

        $instance = $this->app->make($attribute->handlerClass, ...$references);

        $callback = ($attribute->handlerMethod === '__invoke') ? $instance : $instance->{$attribute->handlerMethod}(...);

        return new MessageHandler($callback, $attribute->priority, $queue);
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

    protected function determineQueue(null|string|array $queue): ?array
    {
        return match (true) {
            is_string($queue) => $this->app[$queue]->jsonSerialize(),
            is_array($queue) => (new QueueOption(...$queue))->jsonSerialize(), // todo queue option from config
            default => null,
        };
    }

    private function assertCountHandlerPerType(MessageHandlerAttribute $data): void
    {
        if ($data->type === DomainType::EVENT->value) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query is allowed');
    }
}
