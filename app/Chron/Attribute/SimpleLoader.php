<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Attribute\MessageHandler\AsMessageHandler;
use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use App\Chron\Reporter\DomainType;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function implode;
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
        /** @var ReflectionAttribute $attribute */
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            $this->assertValidInstance($instance);

            $instanceMethod = $this->determineHandlerMethod($instance->method, $reflectionMethod);

            $messageHandler = new MessageHandlerData($reflectionClass, $instance, $instanceMethod);

            $this->addMessage($messageHandler);
        }
    }

    protected function addMessage(MessageHandlerData $data): void
    {
        if (! $this->messages->has($data->handles)) {
            $this->messages->put($data->handles, [$data->priority => $data]);
        } else {
            $this->assertCountHandlerPerType($data);

            $messageHandlers = $this->messages->get($data->handles);

            if (isset($messageHandlers[$data->priority])) {
                throw new RuntimeException("Duplicate priority $data->priority for $data->handles");
            }

            $messageHandlers[$data->priority] = $data;

            uksort($messageHandlers, fn (int $a, int $b): int => $a <=> $b);

            $this->messages->put($data->handles, $messageHandlers);
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

    private function assertCountHandlerPerType(MessageHandlerData $data): void
    {
        $type = $data->type;

        if ($type === DomainType::EVENT) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query allowed');
    }

    private function assertValidInstance(object $instance): void
    {
        if ($instance::class === AsMessageHandler::class) {
            $available = [AsCommandHandler::class, AsQueryHandler::class, AsEventHandler::class];

            throw new RuntimeException('Use one of child attribute classes of '.(implode(', ', $available)));
        }
    }
}
