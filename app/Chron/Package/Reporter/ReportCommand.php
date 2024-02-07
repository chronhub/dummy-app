<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter;

use App\Chron\Package\Attribute\Reporter\AsReporter;
use App\Chron\Package\Attribute\Reporter\Mode;
use App\Chron\Package\Reporter\Producer\QueueOption;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\DelegateToQueue;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.command.default',
    type: DomainType::COMMAND,
    mode: Mode::ASYNC,
    defaultQueue: QueueOption::class
)]
final class ReportCommand implements Reporter
{
    use DelegateToQueue;
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        $this->queueAndProcess($message);
    }
}
