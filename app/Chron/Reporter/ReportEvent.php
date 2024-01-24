<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Reporter\Manager\GenericReporterSubscriberManager;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.event.default',
    type: DomainType::EVENT,
    sync: true,
    subscribers: GenericReporterSubscriberManager::class,
)]
final class ReportEvent implements Reporter
{
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        $this->dispatch($message);
    }
}
