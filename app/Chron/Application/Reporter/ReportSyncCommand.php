<?php

declare(strict_types=1);

namespace App\Chron\Application\Reporter;

use Storm\Contract\Reporter\Reporter;
use Storm\Message\DomainType;
use Storm\Reporter\Attribute\AsReporter;
use Storm\Reporter\Attribute\Mode;
use Storm\Reporter\DelegateToQueue;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.command.sync',
    type: DomainType::COMMAND,
    mode: Mode::SYNC,
)]
final class ReportSyncCommand implements Reporter
{
    use DelegateToQueue;
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        $this->queueAndProcess($message);
    }
}
