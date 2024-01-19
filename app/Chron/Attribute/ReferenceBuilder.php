<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Container\Container;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;

use function sprintf;

/**
 * @template Ref of array<string,object>
 */
class ReferenceBuilder
{
    public function __construct(protected Container $container)
    {
    }

    /**
     * Find references in reflection class constructor
     *
     * @return array<Ref>|array
     */
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
                $references[] = $this->makeReference(
                    $attribute->newInstance()->name,
                    $parameter->getName(),
                    $reflectionClass->getName()
                );
            }
        }

        return $references;
    }

    /**
     * Build reference
     *
     * @return array<Ref>
     *
     * @throws RuntimeException When reference id is not found in container
     */
    protected function makeReference(string $referenceId, string $parameterName, string $handlerClass): array
    {
        if (! $this->container->bound($referenceId)) {
            throw new RuntimeException(sprintf('Reference %s not found in message handler class %s', $referenceId, $handlerClass));
        }

        return [$parameterName => $this->container[$referenceId]];
    }
}
