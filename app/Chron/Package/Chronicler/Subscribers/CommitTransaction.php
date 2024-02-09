<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use Closure;
use Storm\Chronicler\Exceptions\TransactionNotStarted;
use Storm\Contract\Tracker\StreamStory;
use Storm\Contract\Tracker\TransactionalStreamStory;

#[AsStreamSubscriber(
    event: TransactionalEventableChronicler::COMMIT_TRANSACTION_EVENT,
    chronicler: 'chronicler.event.transactional.*'
)]
final readonly class CommitTransaction
{
    public function __invoke(TransactionalChronicler $chronicler): Closure
    {
        return static function (TransactionalStreamStory|StreamStory $story) use ($chronicler): void {
            try {
                $chronicler->commitTransaction();
            } catch (TransactionNotStarted $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
