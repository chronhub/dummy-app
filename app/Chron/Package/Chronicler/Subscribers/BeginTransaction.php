<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use Closure;
use Storm\Chronicler\Exceptions\TransactionAlreadyStarted;
use Storm\Contract\Tracker\TransactionalStreamStory;

#[AsStreamSubscriber(
    event: TransactionalEventableChronicler::BEGIN_TRANSACTION_EVENT,
    chronicler: 'chronicler.event.transactional.*'
)]
final class BeginTransaction
{
    public function __invoke(TransactionalChronicler $chronicler): Closure
    {
        return static function (TransactionalStreamStory $story) use ($chronicler): void {
            try {
                $chronicler->beginTransaction();
            } catch (TransactionAlreadyStarted $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
