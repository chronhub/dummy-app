<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\Reporter\AsReporter;
use App\Chron\Reporter\Manager\GenericReporterSubscriberManager;
use React\Promise\PromiseInterface;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.query.default',
    type: DomainType::QUERY,
    sync: true,
    subscribers: GenericReporterSubscriberManager::class,
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
