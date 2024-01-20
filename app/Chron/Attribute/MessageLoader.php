<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Attribute\MessageHandler\AsMessageHandler;
use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class MessageLoader
{
    /**
     * @var Collection<array<ReflectionClass, ?ReflectionMethod, AsCommandHandler|AsEventHandler|AsQueryHandler>
     */
    protected Collection $messages;

    public function __construct(protected ClassMap $loader)
    {
        $this->messages = new Collection();
    }

    public function getMessages(): Collection
    {
        $this->loadAttributes(collect($this->loader->classes));

        return $this->messages;
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

        $this->processAttributes($attributes, $reflectionClass, null);
    }

    protected function findAttributesInMethods(ReflectionClass $reflectionClass): void
    {
        $methods = ReflectionUtil::attributesInMethods($reflectionClass, AsMessageHandler::class);

        $methods->each(function (array $attributes) use ($reflectionClass): void {
            if ($attributes[0] instanceof ReflectionMethod === false || $attributes[1]->isEmpty()) {
                return;
            }

            $this->processAttributes($attributes[1], $reflectionClass, $attributes[0]);
        });
    }

    protected function processAttributes(Collection $attributes, ReflectionClass $reflectionClass, ?ReflectionMethod $reflectionMethod): void
    {
        $attributes
            ->map(fn (ReflectionAttribute $attribute): object => $attribute->newInstance())
            ->each(function (AsCommandHandler|AsEventHandler|AsQueryHandler $attribute) use ($reflectionClass, $reflectionMethod): void {
                $this->messages->push([$reflectionClass, $reflectionMethod, $attribute]);
            });
    }
}
