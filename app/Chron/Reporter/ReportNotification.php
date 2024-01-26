<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Attribute\Reporter\Enqueue;
use App\Chron\Reporter\Manager\SubscriberManager;
use App\Chron\Reporter\Producer\QueueOption;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.event.notification',
    type: DomainType::EVENT,
    enqueue: Enqueue::ASYNC,
    subscribers: SubscriberManager::class,
    defaultQueue: QueueOption::class
)]
class ReportNotification implements Reporter
{
    use HasConstructableReporter;

    public function relay(object|array $message): void
    {
        logger('ReportNotification:');

        $this->dispatch($message);
    }
}
