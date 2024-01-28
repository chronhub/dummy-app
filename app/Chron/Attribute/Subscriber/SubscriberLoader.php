<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use App\Chron\Attribute\AbstractLoader;
use App\Chron\Attribute\Catalog;
use App\Chron\Attribute\Reference\ReferenceBuilder;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class SubscriberLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsReporterSubscriber::class;

    /**
     * @var Collection<SubscriberAttribute>
     */
    protected Collection $attributes;

    public function __construct(
        protected Catalog $catalog,
        protected ReferenceBuilder $referenceBuilder,
    ) {
        $this->attributes = new Collection();
    }

    public function getAttributes(): Collection
    {
        $this->loadAttributes($this->catalog->getSubscriberClasses());

        return $this->attributes;
    }

    protected function loadAttributes(Collection $classes): void
    {
        $classes
            ->map(fn (string $class): ReflectionClass => new ReflectionClass($class))
            ->each(function (ReflectionClass $reflectionClass): void {
                $this->findAttributesInClass($reflectionClass, self::ATTRIBUTE_NAME);

                $this->findAttributesInMethods($reflectionClass, self::ATTRIBUTE_NAME);
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
                        $this->determineMethodName($attribute->method, $reflectionMethod),
                        $attribute->priority,
                        $attribute->name,
                        $attribute->autowire,
                        $this->referenceBuilder->fromConstructor($reflectionClass)
                    ),
                );
            });
    }
}
