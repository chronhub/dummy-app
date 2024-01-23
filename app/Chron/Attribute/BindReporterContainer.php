<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Reporter\ReporterMap;
use Illuminate\Contracts\Foundation\Application;

class BindReporterContainer
{
    public function __construct(
        protected ReporterMap $reporterMap,
        protected Application $app
    ) {
    }

    public function autoBind(): void
    {
        $this->reporterMap->load();
    }
}
