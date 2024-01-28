<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Attribute\AbstractLoader;
use App\Chron\Attribute\Catalog;
use App\Chron\Attribute\Messaging\MessageAttribute;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class ReporterLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsReporter::class;

    /**
     * @var Collection<MessageAttribute>
     */
    protected Collection $attributes;

    public function __construct(protected Catalog $catalog)
    {
        $this->attributes = new Collection();
    }

    public function getAttributes(): Collection
    {
        $this->loadAttributes($this->catalog->getReporterClasses());

        return $this->attributes;
    }

    protected function loadAttributes(Collection $classes): void
    {
        $classes
            ->map(fn (string $class): ReflectionClass => new ReflectionClass($class))
            ->each(function (ReflectionClass $reflectionClass): void {
                $this->findAttributesInClass($reflectionClass, self::ATTRIBUTE_NAME);
            });
    }

    protected function processAttributes(ReflectionClass $reflectionClass, ?ReflectionMethod $reflectionMethod, Collection $attributes): void
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
                        $attribute->listeners,
                        $attribute->defaultQueue,
                        $attribute->tracker,
                    ),
                );
            });
    }
}
