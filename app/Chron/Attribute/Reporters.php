<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Reporter\ReporterMap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

class Reporters
{
    public function __construct(
        protected ReporterMap $reporterMap,
        protected Application $app
    ) {
    }

    public function bootstrap(): void
    {
        $this->reporterMap->load();
    }

    public function getEntries(): Collection
    {
        return $this->reporterMap->getEntries();
    }

    public function getQueues(): array
    {
        return $this->reporterMap->getDeclaredQueues();
    }
}
