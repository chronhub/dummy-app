<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;

use function sprintf;

class TagContainer
{
    public const TAG = 'message.handler.%s';

    public function __construct(
        protected SimpleLoader $simpleLoader,
        protected Container $container
    ) {
    }

    public function find(string $messageName): iterable
    {
        $tagName = $this->nameTag($messageName);

        return $this->container->tagged($tagName);
    }

    public function autoTag(): void
    {
        $this->simpleLoader->register();

        $handlers = [];

        foreach ($this->simpleLoader->attributes as $messageName => $messageHandler) {

            foreach ($messageHandler as $key => $handler) {
                [$reflectionHandlerClass, $handlerMethod] = $handler;

                $serviceId = $this->nameTag($messageName, $key);

                $this->container->bind($serviceId, fn () => $this->newMessageHandlerInstance($reflectionHandlerClass, $handlerMethod));

                if (isset($handlers[$messageName])) {
                    $handlers[$messageName][] = $serviceId;

                    continue;
                }

                $handlers[$messageName] = [$serviceId];
            }
        }

        foreach ($handlers as $messageName => $messageHandlers) {
            $this->container->tag($messageHandlers, $this->nameTag($messageName));
        }
    }

    protected function nameTag(string $messageName, ?int $key = null): string
    {
        $tagName = sprintf(self::TAG, Str::remove('\\', Str::snake($messageName)));

        if ($key !== null) {
            $tagName .= '_'.$key;
        }

        return $tagName;
    }

    protected function newMessageHandlerInstance(ReflectionClass $messageHandler, ?string $method): callable
    {
        $references = $this->lookForConstructorReference($messageHandler);

        $instance = $this->container->make($messageHandler->getName(), ...$references);

        if ($method === null || $method === '__invoke') {
            return $instance;
        }

        return $instance->{$method}(...);
    }

    protected function lookForConstructorReference(ReflectionClass $reflectionClass): array
    {
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $parameters = $constructor->getParameters();

        $references = [];

        foreach ($parameters as $parameter) {
            $attributes = $parameter->getAttributes(Reference::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $references[] = $this->makeReference($instance->name, $parameter->getName());
            }
        }

        return $references;
    }

    protected function makeReference(string $referenceId, string $parameterName): array
    {
        if (! $this->container->bound($referenceId)) {
            throw new RuntimeException(sprintf('Reference %s not found in message handler', $referenceId));
        }

        return [$parameterName => $this->container[$referenceId]];
    }
}
