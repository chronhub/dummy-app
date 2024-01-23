<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Reporter\Manager\ReporterSubscriberManager;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\DelegateToQueue;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.command.mine',
    type: DomainType::COMMAND,
    sync: true,
    subscribers: ReporterSubscriberManager::class,
)]
class MyReportCommand implements Reporter
{
    use DelegateToQueue;
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        $this->queueAndProcess($message);
    }
}
