<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\StreamSubscriber;

use App\Chron\Package\Attribute\AbstractLoader;
use App\Chron\Package\Attribute\Catalog;
use App\Chron\Package\Attribute\Reference\ReferenceBuilder;
use App\Chron\Package\Attribute\Subscriber\SubscriberAttribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class StreamSubscriberLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsStreamSubscriber::class;

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
        $this->loadAttributes($this->catalog->getStreamSubscriberClasses());

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
            ->each(function (AsStreamSubscriber $attribute) use ($reflectionClass, $reflectionMethod): void {
                $this->attributes->push(
                    new StreamSubscriberAttribute(
                        $attribute->event,
                        $reflectionClass->getName(),
                        Arr::wrap($attribute->chronicler),
                        $this->determineMethodName($attribute->method, $reflectionMethod),
                        $attribute->priority,
                        $attribute->autowire,
                        $this->referenceBuilder->fromConstructor($reflectionClass)
                    ),
                );
            });
    }
}
