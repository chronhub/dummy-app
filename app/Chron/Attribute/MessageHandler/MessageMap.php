<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Reporter\DomainType;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use RuntimeException;

use function uksort;

class MessageMap
{
    /**
     * @var Collection{string, array<MessageHandlerAttribute}>
     */
    protected Collection $entries;

    protected Closure $prefixResolver;

    public function __construct(
        protected MessageHandlerLoader $loader,
        protected QueueResolver $queueResolver,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(): void
    {
        $this->entries = $this->loader->getAttributes()
            ->each(fn (MessageHandlerAttribute $attribute) => $this->build($attribute))
            ->groupBy('handles')
            ->map(fn (Collection $messageHandlers): array => $this->bind($messageHandlers));
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function setPrefixResolver(Closure $prefixResolver): void
    {
        $this->prefixResolver = $prefixResolver;
    }

    protected function build(MessageHandlerAttribute $attribute): void
    {
        // todo reporter id for each message handler must be the same
        //  our strategy to dispatch can fit many reporters

        // todo how to dispatch event with no handler
        //  can use notification reporter with a no handler property
        //  they could all end his the same handler at least to log it

        // todo when event handlers are all completed, we should dispatch an internal event
        // probably with laravel event dispatcher

        if (! $this->entries->has($attribute->handles)) {
            $this->entries->put($attribute->handles, [$attribute->priority => $attribute]);

            return;
        }

        $this->assertShouldHaveOneHandlerDependsOnType($attribute);

        $handlers = $this->entries->get($attribute->handles);

        if (isset($handlers[$attribute->priority])) {
            throw new RuntimeException("Duplicate priority $attribute->priority for $attribute->handles");
        }

        $handlers[$attribute->priority] = $attribute;

        uksort($handlers, fn (int $a, int $b): int => $a <=> $b);

        $this->entries->put($attribute->handles, $handlers);
    }

    protected function bind(Collection $messageHandlers): array
    {
        return $messageHandlers->map(function (MessageHandlerAttribute $attribute) {
            $abstract = $this->tagConcrete($attribute->handles, $attribute->priority);

            $queue = $this->queueResolver->make($attribute->reporterId, $attribute->queue);

            $this->app->bind($abstract, fn (): callable => $this->newHandlerInstance($attribute, $queue));

            return $attribute->newInstance($abstract, $this->tagConcrete($attribute->handles), $queue);
        })->toArray();
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
        $arguments = [];

        foreach ($references as $parameter) {
            foreach ($parameter as [$parameterName, $serviceId]) {
                $arguments[] = [$parameterName => $this->app[$serviceId]];
            }
        }

        return $arguments;
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        return ($this->prefixResolver)($concrete, $key);
    }

    protected function formatName(string $HandlerClass, string $handlerMethod): string
    {
        return $HandlerClass.'@'.$handlerMethod;
    }

    protected function assertShouldHaveOneHandlerDependsOnType(MessageHandlerAttribute $data): void
    {
        if ($data->type === DomainType::EVENT->value) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query types is allowed');
    }
}
