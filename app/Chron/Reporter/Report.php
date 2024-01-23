<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Support\Facades\Facade;
use Storm\Contract\Reporter\Reporter;

/**
 * @method static Reporter get(string $name)
 */
class Report extends Facade
{
    public const REPORTER_ID = 'reporter.manager';

    protected static function getFacadeAccessor(): string
    {
        return self::REPORTER_ID;
    }
}
