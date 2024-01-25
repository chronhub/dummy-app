<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Attribute\MessageHandler\MessageHandlerAttribute;
use App\Chron\Attribute\ReflectionUtil;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;

class ReporterLoader
{
    /**
     * @var Collection<array<MessageHandlerAttribute>>
     */
    protected Collection $attributes;

    public function __construct(protected ReporterClassMap $loader)
    {
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
            });
    }

    protected function findAttributesInClass(ReflectionClass $reflectionClass): void
    {
        $attributes = ReflectionUtil::attributesInClass($reflectionClass, AsReporter::class);

        if ($attributes->isEmpty()) {
            return;
        }

        $this->processAttributes($reflectionClass, $attributes);
    }

    protected function processAttributes(ReflectionClass $reflectionClass, Collection $attributes): void
    {
        $attributes
            ->map(fn (ReflectionAttribute $attribute): object => $attribute->newInstance())
            ->each(function (AsReporter $attribute) use ($reflectionClass): void {
                $this->attributes->push(
                    new ReporterAttribute(
                        $attribute->id,
                        $reflectionClass->getName(),
                        $attribute->type->value,
                        $attribute->enqueue->value,
                        $attribute->subscribers,
                        $attribute->listeners,
                        $attribute->defaultQueue,
                        $attribute->tracker,
                    ),
                );
            });
    }
}
