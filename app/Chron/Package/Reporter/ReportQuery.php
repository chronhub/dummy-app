<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter;

use App\Chron\Package\Attribute\Reporter\AsReporter;
use App\Chron\Package\Attribute\Reporter\Mode;
use React\Promise\PromiseInterface;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\HasConstructableReporter;

#[AsReporter(
    id: 'reporter.query.default',
    type: DomainType::QUERY,
    mode: Mode::SYNC,
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
