<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;

use function uksort;

class SimpleLoader
{
    public Collection $attributes;

    public function __construct(protected ClassMap $loader)
    {
        $this->attributes = new Collection();
    }

    public function register(): void
    {
        $classes = $this->loader->classes;

        $this->findAttributeInClass($classes);
    }

    protected function findAttributeInClass(array $classes): void
    {
        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(AsMessageHandler::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $messageHandler = [$reflectionClass, $instance->method];

                $this->addAttribute($instance->handles, $messageHandler, $instance->priority);
            }

            $this->findAttributeInMethods($reflectionClass);
        }
    }

    protected function findAttributeInMethods(ReflectionClass $reflectionClass): void
    {
        $reflectionMethods = $reflectionClass->getMethods();

        foreach ($reflectionMethods as $reflectionMethod) {
            $attributes = $reflectionMethod->getAttributes(AsMessageHandler::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $messageHandler = [$reflectionClass, $instance->method];

                $this->addAttribute($instance->handles, $messageHandler, $instance->priority);
            }
        }
    }

    protected function addAttribute(string $messageName, array $messageHandler, ?int $priority = 0): void
    {
        if (! $this->attributes->has($messageName)) {
            $this->attributes->put($messageName, [$priority => $messageHandler]);
        } else {
            $values = $this->attributes->get($messageName);

            $values[$priority] = $messageHandler;

            uksort($values, fn ($a, $b) => $a <=> $b);

            $this->attributes->put($messageName, $values);
        }
    }
}
