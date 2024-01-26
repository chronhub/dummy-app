<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Attribute\Reporter\Enqueue;
use App\Chron\Reporter\Manager\SubscriberManager;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.event.default',
    type: DomainType::EVENT,
    enqueue: Enqueue::SYNC,
    subscribers: SubscriberManager::class,
)]
final class ReportEvent implements Reporter
{
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        logger('ReportEvent:');
        $this->dispatch($message);
    }
}
