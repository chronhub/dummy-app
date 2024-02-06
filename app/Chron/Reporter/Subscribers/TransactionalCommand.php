<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\TransactionalEventableChronicler;
use Closure;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

final readonly class TransactionalCommand
{
    public function __construct(private Chronicler $chronicler)
    {
    }

    #[AsReporterSubscriber(
        supports: ['reporter.command.default'],
        event: Reporter::DISPATCH_EVENT,
        method: 'startTransaction',
        priority: 30000,
        autowire: true,
    )]
    public function startTransaction(): Closure
    {
        return function (MessageStory $story): void {
            if ($this->chronicler instanceof TransactionalEventableChronicler) {
                $message = $story->message();

                $this->chronicler->beginTransaction();

                logger('Start transactional for command: '.$message->name());
            }
        };
    }

    #[AsReporterSubscriber(
        supports: ['reporter.command.default'],
        event: Reporter::FINALIZE_EVENT,
        method: 'finalizeTransaction',
        priority: 100,
        autowire: true,
    )]
    public function finalizeTransaction(): Closure
    {
        return function (MessageStory $story): void {
            if (! $this->chronicler instanceof TransactionalEventableChronicler) {
                return;
            }

            $message = $story->message();

            if ($story->hasException()) {
                $this->chronicler->rollbackTransaction();

                logger('Rollback transactional for command: '.$message->name(), ['exception' => $story->exception()->getMessage()]);
            } else {
                $this->chronicler->commitTransaction();

                logger('Commit transactional for command: '.$message->name());
            }
        };
    }
}
