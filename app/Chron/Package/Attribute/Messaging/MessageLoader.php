<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Messaging;

use App\Chron\Package\Attribute\AbstractLoader;
use App\Chron\Package\Attribute\Catalog;
use App\Chron\Package\Attribute\Reference\ReferenceBuilder;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class MessageLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsMessageHandler::class;

    /**
     * @var Collection<array<MessageAttribute>>
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
        $this->loadAttributes($this->catalog->getMessageHandlersClasses());

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
            ->each(function (AsCommandHandler|AsEventHandler|AsQueryHandler $attribute) use ($reflectionClass, $reflectionMethod): void {
                $this->attributes->push(
                    new MessageAttribute(
                        $attribute->reporter,
                        $reflectionClass->getName(),
                        $this->determineMethodName($attribute->method, $reflectionMethod),
                        $attribute->handles,
                        $attribute->fromQueue,
                        $attribute->priority,
                        $attribute->type()->value,
                        $this->referenceBuilder->fromConstructor($reflectionClass)
                    ),
                );
            });
    }
}