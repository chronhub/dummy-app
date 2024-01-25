<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Attribute\ReferenceBuilder;
use App\Chron\Attribute\ReflectionUtil;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class MessageHandlerLoader
{
    /**
     * @var Collection<array<MessageHandlerAttribute>>
     */
    protected Collection $attributes;

    public function __construct(
        protected MessageHandlerClassMap $loader,
        protected ReferenceBuilder $referenceBuilder,
    ) {
        $this->attributes = new Collection();
    }

    public function getAttributes(): Collection
    {
        $this->loadAttributes($this->loader->getClasses());

        return $this->attributes;
    }

    protected function loadAttributes(Collection $classes): void
    {
        $classes
            ->map(fn (string $class): ReflectionClass => new ReflectionClass($class))
            ->each(function (ReflectionClass $reflectionClass): void {
                $this->findAttributesInClass($reflectionClass);

                $this->findAttributesInMethods($reflectionClass);
            });
    }

    protected function findAttributesInClass(ReflectionClass $reflectionClass): void
    {
        $attributes = ReflectionUtil::attributesInClass($reflectionClass, AsMessageHandler::class);

        if ($attributes->isEmpty()) {
            return;
        }

        $this->processAttributes($reflectionClass, $attributes, null);
    }

    protected function findAttributesInMethods(ReflectionClass $reflectionClass): void
    {
        $methods = ReflectionUtil::attributesInMethods($reflectionClass, AsMessageHandler::class);

        $methods->each(function (array $attributes) use ($reflectionClass): void {
            if ($attributes[0] instanceof ReflectionMethod && $attributes[1]->isNotEmpty()) {
                $this->processAttributes($reflectionClass, $attributes[1], $attributes[0]);
            }
        });
    }

    protected function processAttributes(ReflectionClass $reflectionClass, Collection $attributes, ?ReflectionMethod $reflectionMethod): void
    {
        $attributes
            ->map(fn (ReflectionAttribute $attribute): object => $attribute->newInstance())
            ->each(function (AsCommandHandler|AsEventHandler|AsQueryHandler $attribute) use ($reflectionClass, $reflectionMethod): void {
                $this->attributes->push(
                    new MessageHandlerAttribute(
                        $attribute->reporter,
                        $reflectionClass->getName(),
                        $this->determineHandlerMethod($attribute->method, $reflectionMethod),
                        $attribute->handles,
                        $attribute->fromQueue,
                        $attribute->priority,
                        $attribute->type()->value,
                        $this->referenceBuilder->fromConstructor($reflectionClass)
                    ),
                );
            });
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
