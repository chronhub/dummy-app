<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\ReportCommand;
use App\Chron\Reporter\ReportEvent;
use App\Chron\Reporter\ReportNotification;
use App\Chron\Reporter\ReportQuery;
use Illuminate\Support\Collection;

class ReporterClassMap
{
    /**
     * @var array<class-string>
     */
    protected array $classes = [
        ReportCommand::class,
        ReportNotification::class,
        ReportEvent::class,
        ReportQuery::class,
    ];

    /**
     * @return Collection<class-string>
     */
    public function getClasses(): Collection
    {
        return collect($this->classes);
    }
}
