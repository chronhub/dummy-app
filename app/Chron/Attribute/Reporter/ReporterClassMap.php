<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\MyReportCommand;

class ReporterClassMap
{
    public array $classes = [
        MyReportCommand::class,
    ];
}
