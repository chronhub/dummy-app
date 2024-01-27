<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use App\Chron\Attribute\Reference\ReferenceBuilder;
use App\Chron\Attribute\ReflectionUtil;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class SubscriberLoader
{
    /**
     * @var Collection<SubscriberAttribute>
     */
    protected Collection $attributes;

    public function __construct(
        protected SubscriberClassMap $catalog,
        protected ReferenceBuilder $referenceBuilder,
    ) {
        $this->attributes = new Collection();
    }

    public function getAttributes(): Collection
    {
        $this->loadAttributes($this->catalog->getClasses());

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
        $attributes = ReflectionUtil::attributesInClass($reflectionClass, AsReporterSubscriber::class);

        if ($attributes->isEmpty()) {
            return;
        }

        $this->processAttributes($reflectionClass, null, $attributes);
    }

    protected function findAttributesInMethods(ReflectionClass $reflectionClass): void
    {
        $methods = ReflectionUtil::attributesInMethods($reflectionClass, AsReporterSubscriber::class);

        $methods->each(function (array $reflection): void {
            [$reflectionClass, $reflectionMethod, $attributes] = $reflection;

            if ($attributes->isNotEmpty()) {
                $this->processAttributes($reflectionClass, $reflectionMethod, $attributes);
            }
        });
    }

    protected function processAttributes(ReflectionClass $reflectionClass, ?ReflectionMethod $reflectionMethod, Collection $attributes): void
    {
        $attributes
            ->map(fn (ReflectionAttribute $attribute): object => $attribute->newInstance())
            ->each(function (AsReporterSubscriber $attribute) use ($reflectionClass, $reflectionMethod): void {
                $this->attributes->push(
                    new SubscriberAttribute(
                        $reflectionClass->getName(),
                        $attribute->event,
                        $attribute->supports,
                        $this->determineHandlerMethod($attribute->method, $reflectionMethod),
                        $attribute->priority,
                        $attribute->name,
                        $attribute->autowire,
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
