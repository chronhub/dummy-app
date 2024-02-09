<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\AggregateRepository;

use App\Chron\Package\Attribute\AbstractLoader;
use App\Chron\Package\Attribute\Catalog;
use App\Chron\Package\Attribute\Reference\ReferenceBuilder;
use App\Chron\Package\Attribute\Subscriber\SubscriberAttribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function count;

class AggregateRepositoryLoader extends AbstractLoader
{
    public const ATTRIBUTE_NAME = AsAggregateRepository::class;

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
        $this->loadAttributes($this->catalog->getAggregateRepositoryClasses());

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
            ->each(function (AsAggregateRepository $attribute) use ($reflectionClass): void {
                $abstract = $this->determineAbstract($attribute->abstract, $reflectionClass->getInterfaceNames());

                $this->attributes->push(
                    new AggregateRepositoryAttribute(
                        $reflectionClass->getName(),
                        $abstract,
                        $attribute->chronicler,
                        $attribute->streamName,
                        Arr::wrap($attribute->aggregateRoot),
                        $attribute->messageDecorator,
                        $attribute->factory,
                        $this->referenceBuilder->fromConstructor($reflectionClass)
                    ),
                );
            });
    }

    protected function determineAbstract(?string $abstract, array $interfaces): string
    {
        if ($abstract !== null) {
            return $abstract;
        }

        if (count($interfaces) === 1) {
            return $interfaces[0];
        }

        throw new RuntimeException('Could not determine abstract for aggregate repository, no interface or too many found, please specify one manually.');
    }
}
