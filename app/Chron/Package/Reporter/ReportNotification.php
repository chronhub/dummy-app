<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter;

use App\Chron\Package\Attribute\Reporter\AsReporter;
use App\Chron\Package\Attribute\Reporter\Mode;
use App\Chron\Package\Reporter\Producer\QueueOption;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.event.notification',
    type: DomainType::EVENT,
    mode: Mode::ASYNC,
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