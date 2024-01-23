<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\ReportCommand;
use App\Chron\Reporter\ReportEvent;
use App\Chron\Reporter\ReportQuery;

class ReporterClassMap
{
    /**
     * @var array<class-string>
     */
    public array $classes = [
        ReportCommand::class,
        ReportEvent::class,
        ReportQuery::class,
    ];
}
