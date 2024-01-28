<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reference;

trait ReferenceResolverTrait
{
    protected function makeParametersFromConstructor(array $references): array
    {
        $arguments = [];

        foreach ($references as $method => $parameter) {
            if ($method !== '__construct') {
                continue;
            }

            foreach ($parameter as [$parameterName, $serviceId]) {
                $arguments[] = [$parameterName => $this->app($serviceId)];
            }
        }

        return $arguments;
    }

    abstract protected function app(string $serviceId): mixed;
}
