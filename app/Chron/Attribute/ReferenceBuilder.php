<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Container\Container;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;

use function sprintf;

class ReferenceBuilder
{
    public function __construct(protected Container $container)
    {
    }

    public function fromConstructor(ReflectionClass $reflectionClass): array
    {
        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $references = [];

        foreach ($constructor->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(Reference::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                $references[] = $this->makeReference($instance->name, $parameter->getName());
            }
        }

        return $references;
    }

    protected function makeReference(string $referenceId, string $parameterName): array
    {
        if (! $this->container->bound($referenceId)) {
            throw new RuntimeException(sprintf('Reference %s not found in message handler', $referenceId));
        }

        return [$parameterName => $this->container[$referenceId]];
    }
}
