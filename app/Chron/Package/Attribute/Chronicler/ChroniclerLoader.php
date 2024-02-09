<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Chronicler;

use App\Chron\Package\Attribute\AbstractLoader;
use App\Chron\Package\Attribute\Catalog;
use App\Chron\Package\Attribute\Messaging\MessageAttribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class ChroniclerLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsChronicler::class;

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
        $this->loadAttributes($this->catalog->getChroniclerClasses());

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
            ->each(function (AsChronicler $attribute) use ($reflectionClass): void {
                $this->attributes->push(
                    new ChroniclerAttribute(
                        $reflectionClass->getName(),
                        $attribute->connection,
                        $attribute->tableName,
                        $attribute->persistence,
                        $attribute->eventable,
                        $attribute->transactional,
                        $attribute->evenStreamProvider,
                        $attribute->streamEventLoader,
                        $attribute->abstract,
                        Arr::wrap($attribute->subscribers),
                        $attribute->decoratorFactory,
                    ),
                );
            });
    }
}
