<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reference;

use Illuminate\Contracts\Container\Container;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

use function is_string;
use function sprintf;

/**
 * @template Ref of array<string, string>
 */
class ReferenceBuilder
{
    public function __construct(protected Container $container)
    {
    }

    /**
     * Find references in reflection class constructor
     *
     * @return array<array{'__construct': string, Ref}>|array
     *
     * @throws ReflectionException
     */
    public function fromConstructor(string|ReflectionClass $reflectionClass): array
    {
        if (is_string($reflectionClass)) {
            $reflectionClass = new ReflectionClass($reflectionClass);
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $references = [];

        foreach ($constructor->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(Reference::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $this->assertReferenceExistsInContainer($instance->name, $reflectionClass->getName());

                $references[] = [$parameter->getName(), $instance->name];
            }
        }

        return [$constructor->getName() => $references];
    }

    /**
     * Assert that reference exists in container
     *
     * @throws RuntimeException When reference id is not found in container
     */
    protected function assertReferenceExistsInContainer(string $referenceId, string $handlerClass): void
    {
        if (! $this->container->bound($referenceId)) {
            throw new RuntimeException(sprintf('Reference %s not found in message handler class %s', $referenceId, $handlerClass));
        }
    }
}
