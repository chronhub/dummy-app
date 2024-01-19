<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function uksort;

class SimpleLoader
{
    public Collection $messages;

    public function __construct(protected ClassMap $loader)
    {
        $this->messages = new Collection();
    }

    public function load(): void
    {
        $this->findAttributesInClasses($this->loader->classes);
    }

    protected function findAttributesInClasses(array $classes): void
    {
        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);

            $this->findAttributesInClass($reflectionClass);
            $this->findAttributesInMethods($reflectionClass);
        }
    }

    protected function findAttributesInClass(ReflectionClass $reflectionClass): void
    {
        $this->processAttributes(
            $reflectionClass->getAttributes(
                AsMessageHandler::class,
                ReflectionAttribute::IS_INSTANCEOF),
            $reflectionClass,
            null
        );
    }

    protected function findAttributesInMethods(ReflectionClass $reflectionClass): void
    {
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $this->processAttributes(
                $reflectionMethod->getAttributes(
                    AsMessageHandler::class,
                    ReflectionAttribute::IS_INSTANCEOF
                ),
                $reflectionClass,
                $reflectionMethod
            );
        }
    }

    protected function processAttributes(array $attributes, ReflectionClass $reflectionClass, ?ReflectionMethod $reflectionMethod): void
    {
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            $instanceMethod = $this->determineHandlerMethod($instance->method, $reflectionMethod);

            $messageHandler = new MessageHandlerData($reflectionClass, $instanceMethod, $instance->reporter, $instance->fromQueue);

            $this->addMessage($instance->handles, $messageHandler, $instance->priority);
        }
    }

    protected function addMessage(string $messageName, MessageHandlerData $messageHandler, ?int $priority = 0): void
    {
        if (! $this->messages->has($messageName)) {
            $this->messages->put($messageName, [$priority => $messageHandler]);
        } else {
            $messageHandlers = $this->messages->get($messageName);

            if (isset($messageHandlers[$priority])) {
                throw new RuntimeException("Duplicate priority $priority for $messageName");
            }

            $messageHandlers[$priority] = $messageHandler;

            uksort($messageHandlers, fn (int $a, int $b): int => $a <=> $b);

            $this->messages->put($messageName, $messageHandlers);
        }
    }

    protected function determineHandlerMethod(?string $handlerMethod, ?ReflectionMethod $reflectionMethod): string
    {
        return match (true) {
            $handlerMethod !== null => $handlerMethod,
            $reflectionMethod !== null => $reflectionMethod->getName(),
            default => '__invoke',
        };
    }
}
