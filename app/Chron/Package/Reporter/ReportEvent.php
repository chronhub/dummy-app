<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter;

use App\Chron\Package\Attribute\Reporter\AsReporter;
use App\Chron\Package\Attribute\Reporter\Mode;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.event.default',
    type: DomainType::EVENT,
    mode: Mode::SYNC,
)]
final class ReportEvent implements Reporter
{
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        $this->dispatch($message);
    }
}
