<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Attribute\Reporter\Enqueue;
use App\Chron\Reporter\Manager\SubscriberManager;
use App\Chron\Reporter\Producer\QueueOption;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\DelegateToQueue;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.command.default',
    type: DomainType::COMMAND,
    enqueue: Enqueue::SYNC,
    subscribers: SubscriberManager::class,
    //defaultQueue: QueueOption::class
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
