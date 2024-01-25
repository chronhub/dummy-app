<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Attribute\Reporter\Enqueue;
use App\Chron\Reporter\Manager\SubscriberManager;
use React\Promise\PromiseInterface;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.query.default',
    type: DomainType::QUERY,
    enqueue: Enqueue::SYNC,
    subscribers: SubscriberManager::class,
)]
final class ReportQuery implements Reporter
{
    use HasConstructableReporter;

    public function relay(object|array $message): PromiseInterface
    {
        $story = $this->dispatch($message);

        return $story->promise();
    }
}
