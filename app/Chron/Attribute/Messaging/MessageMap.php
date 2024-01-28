<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Messaging;

use App\Chron\Attribute\Reporter\DeclaredQueue;
use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

use function sprintf;
use function uksort;

class MessageMap
{
    public const HANDLER_TAG_PREFIX = '#';

    public const TAG = 'message.handler.%s';

    protected DeclaredQueue $queueResolver;

    /**
     * @var Collection{string, array<MessageAttribute}>
     */
    protected Collection $entries;

    public function __construct(
        protected MessageLoader $loader,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(DeclaredQueue $declaredQueue): void
    {
        $this->queueResolver = $declaredQueue;

        $this->entries = $this->loader->getAttributes()
            ->each(fn (MessageAttribute $attribute) => $this->build($attribute))
            ->groupBy('handles')
            ->map(fn (Collection $messageHandlers): array => $this->bind($messageHandlers));

        $this->tagMessageHandlers();
    }

    public function find(string $messageName): iterable
    {
        $tagName = $this->tagConcrete($messageName);

        return $this->app->tagged($tagName);
    }

    public function findReporterOfMessage(string $messageName): array
    {
        return $this->entries
            ->filter(fn (array $messageHandlers, string $message) => $message === $messageName)
            ->values()
            ->collapse()
            ->pluck('reporterId')
            ->unique()
            ->toArray();
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    protected function build(MessageAttribute $attribute): void
    {
        // todo reporter id for each message handler must be the same
        //  our strategy to dispatch can fit many reporters

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
        return $messageHandlers->map(function (MessageAttribute $attribute) {
            $abstract = $this->tagConcrete($attribute->handles, $attribute->priority);

            $queue = $this->queueResolver->mergeIfNeeded($attribute->reporterId, $attribute->queue);

            $this->app->bind($abstract, fn (): callable => $this->newHandlerInstance($attribute, $queue));

            return $attribute->newInstance($abstract, $this->tagConcrete($attribute->handles), $queue);
        })->toArray();
    }

    protected function newHandlerInstance(MessageAttribute $attribute, ?array $queue): callable
    {
        $callback = $this->makeCallback($attribute);

        $name = $this->formatName($attribute->handlerClass, $attribute->handlerMethod);

        return new MessageHandler($name, $callback, $attribute->priority, $queue);
    }

    protected function makeCallback(MessageAttribute $attribute): callable
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->handlerClass, ...$parameters);

        return ($attribute->handlerMethod === '__invoke') ? $instance : $instance->{$attribute->handlerMethod}(...);
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

    protected function tagMessageHandlers(): void
    {
        $this->entries
            ->collapse()
            ->groupBy('messageId')
            ->map(fn (Collection $messageHandlers) => $messageHandlers->pluck('handlerId'))
            ->each(fn (Collection $handlerIds, string $messageId) => $this->app->tag($handlerIds->toArray(), $messageId));
    }

    protected function tagConcrete(string $concrete, ?int $priority = null): string
    {
        $concreteTag = sprintf(self::TAG, Str::remove('\\', Str::snake($concrete)));

        if ($priority !== null) {
            return sprintf('%s%s', $concreteTag, self::HANDLER_TAG_PREFIX.$priority);
        }

        return $concreteTag;
    }

    protected function formatName(string $handlerClass, string $handlerMethod): string
    {
        return $handlerClass.'@'.$handlerMethod;
    }

    protected function assertShouldHaveOneHandlerDependsOnType(MessageAttribute $data): void
    {
        if ($data->type === DomainType::EVENT->value) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query types is allowed');
    }
}