<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Support\Facades\Facade;
use Storm\Contract\Reporter\Reporter;

/**
 * @method static Reporter create(string $name, string|DomainType $type)
 * @method static Reporter command(?string $name = null)
 * @method static Reporter event(?string $name = null)
 * @method static Reporter query(?string $name = null)
 * @method static string   getDefaultId(string $type)
 * @method static bool     hasId(string $reporterId, bool $isLoaded = false)
 */
class Report extends Facade
{
    public const REPORTER_ID = 'reporter.manager';

    protected static function getFacadeAccessor(): string
    {
        return self::REPORTER_ID;
    }
}
