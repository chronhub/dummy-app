<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use Illuminate\Contracts\Foundation\Application;
use Storm\Contract\Reporter\Reporter;

final class ReporterManager implements Manager
{
    public function __construct(protected Application $app)
    {
    }

    // tmp
    public function get(string $name): Reporter
    {
        return $this->app[$name];
    }
}
