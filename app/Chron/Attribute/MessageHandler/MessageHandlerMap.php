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
        $this->loader->getAttributes()
            ->each(fn (MessageHandlerAttribute $attribute) => $this->build($attribute));

        $this->map->each(fn (array $messageHandlers, string $messageName) => $this->bind($messageName, $messageHandlers));
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

            return;
        }

        $this->assertShouldHaveOneHandlerDependsOnType($attribute);

        $handlers = $this->map->get($attribute->handles);

        if (isset($handlers[$attribute->priority])) {
            throw new RuntimeException("Duplicate priority $attribute->priority for $attribute->handles");
        }

        $handlers[$attribute->priority] = $attribute;

        uksort($handlers, fn (int $a, int $b): int => $a <=> $b);

        $this->map->put($attribute->handles, $handlers);
    }

    protected function bind(string $name, array $handlers): void
    {
        foreach ($handlers as $priority => $attribute) {
            $handlerId = $this->tagConcrete($name, $priority);

            $queue = $this->determineQueueHandler->make($attribute->reporterId, $attribute->queue);

            $this->app->bind($handlerId, fn (): callable => $this->newHandlerInstance($attribute, $queue));

            $this->updateBinding($name, $handlerId, $attribute, $priority, $queue);
        }
    }

    protected function updateBinding(string $name, string $handlerId, MessageHandlerAttribute $attribute, int $priority, ?array $queue): void
    {
        $this->bindings[$name] = array_merge($this->bindings[$name] ?? [], [$priority => $handlerId]);

        $attribute = $attribute->newInstance($handlerId, $this->tagConcrete($name), $queue);

        $this->entries[$name] = array_merge($this->entries[$name] ?? [], [$attribute]);
    }

    protected function newHandlerInstance(MessageHandlerAttribute $attribute, ?array $queue): callable
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->handlerClass, ...$parameters);

        $callback = ($attribute->handlerMethod === '__invoke') ? $instance : $instance->{$attribute->handlerMethod}(...);

        $name = $this->formatName($attribute->handlerClass, $attribute->handlerMethod);

        return new MessageHandler($name, $callback, $attribute->priority, $queue);
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

    protected function formatName(string $class, string $method): string
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
