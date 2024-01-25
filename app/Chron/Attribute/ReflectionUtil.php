<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class ReflectionUtil
{
    /**
     * @return Collection<ReflectionAttribute>
     */
    public static function attributesInClass(ReflectionClass $reflectionClass, string $attribute): Collection
    {
        return collect($reflectionClass->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * @return Collection<array{0: ReflectionClass, 1: ReflectionMethod, 2: Collection<ReflectionAttribute|empty>}>
     */
    public static function attributesInMethods(ReflectionClass $reflectionClass, string $attribute): Collection
    {
        return collect($reflectionClass->getMethods())->map(
            fn (ReflectionMethod $reflectionMethod): array => [
                $reflectionClass,
                $reflectionMethod,
                collect($reflectionMethod->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)),
            ]
        );
    }
}
