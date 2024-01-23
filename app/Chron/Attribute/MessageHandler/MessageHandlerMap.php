<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Reporter\DomainType;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use RuntimeException;

use function array_merge;
use function uksort;

class MessageHandlerMap
{
    // todo handler is dedicated to a specific reporter,
    //  when find message handler we need to check if reporter is the same

    // todo references in handler methods

    /**
     * @var Collection<string, array<MessageHandlerAttribute>>
     */
    protected Collection $map;

    protected array $bindings;

    protected array $entries;

    protected Closure $prefixResolver;

    public function __construct(
        protected MessageHandlerLoader $loader,
        protected DetermineQueueHandler $determineQueueHandler,
        protected Application $app
    ) {
        $this->map = new Collection();
    }

    public function load(): void
    {
        $this->loader->getAttributes()->each(fn (array $data) => $this->build(...$data));

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

    public function setPrefixResolver(Closure $prefixResolver): void
    {
        $this->prefixResolver = $prefixResolver;
    }

    protected function build(MessageHandlerAttribute $attribute): void
    {
        if (! $this->map->has($attribute->handles)) {
            $this->map->put($attribute->handles, [$attribute->priority => $attribute]);
        } else {
            $this->assertShouldHaveOneHandlerDependsOnType($attribute);

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

            $queue = $this->determineQueueHandler->make($attribute->reporterId, $attribute->queue);

            $this->app->bind($messageHandlerId, fn (): callable => $this->newHandlerInstance($attribute, $queue));

            $this->updateBinding($messageName, $messageHandlerId, $attribute, $priority, $queue);
        }
    }

    protected function updateBinding(string $messageName, string $messageHandlerId, MessageHandlerAttribute $attribute, $priority, ?array $queue): void
    {
        $this->bindings[$messageName] = array_merge($this->bindings[$messageName] ?? [], [$priority => $messageHandlerId]);

        $attribute = $attribute->newInstance($messageHandlerId, $this->tagConcrete($messageName), $queue);

        $this->entries[$messageName] = array_merge($this->entries[$messageName] ?? [], [$attribute]);
    }

    protected function newHandlerInstance(MessageHandlerAttribute $attribute, ?array $queue): callable
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->handlerClass, ...$parameters);

        $callback = ($attribute->handlerMethod === '__invoke') ? $instance : $instance->{$attribute->handlerMethod}(...);

        $messageHandlerName = $this->formatMessageHandlerName($attribute->handlerClass, $attribute->handlerMethod);

        return new MessageHandler($messageHandlerName, $callback, $attribute->priority, $queue);
    }

    protected function makeParametersFromConstructor(array $references): array
    {
        $parameters = [];

        foreach ($references as [$parameterName, $serviceId]) {
            $parameters[] = [$parameterName => $this->app[$serviceId]];
        }

        return $parameters;
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        return ($this->prefixResolver)($concrete, $key);
    }

    protected function formatMessageHandlerName(string $class, string $method): string
    {
        return class_basename($class).'::'.$method;
    }

    protected function assertShouldHaveOneHandlerDependsOnType(MessageHandlerAttribute $data): void
    {
        if ($data->type === DomainType::EVENT->value) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query types is allowed');
    }
}
