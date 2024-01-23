<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;

class ReflectionUtil
{
    public static function attributesInClass(ReflectionClass $reflectionClass, string $attribute): Collection
    {
        return collect($reflectionClass->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF));
    }

    public static function attributesInMethods(ReflectionClass $reflectionClass, string $attribute): Collection
    {
        $methods = $reflectionClass->getMethods();

        if ($methods === []) {
            return collect();
        }

        $attributes = [];

        foreach ($methods as $method) {
            $attributes[] = [$method, collect($method->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF))];
        }

        return collect($attributes);
    }

    public static function attributesInMethod(ReflectionClass $reflectionClass, string $method, string $attribute): Collection
    {
        $reflectionMethod = $reflectionClass->getMethod($method);

        return collect($reflectionMethod->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF));
    }

    public static function attributeInMethod(ReflectionClass $reflectionClass, string $method, string $attribute): ?ReflectionAttribute
    {
        $reflectionMethod = $reflectionClass->getMethod($method);

        $attributes = $reflectionMethod->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0];
    }
}
