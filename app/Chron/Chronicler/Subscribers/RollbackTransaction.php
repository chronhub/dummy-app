<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Subscribers;

use App\Chron\Chronicler\Contracts\TransactionalChronicler;
use Closure;
use Storm\Chronicler\Exceptions\TransactionNotStarted;
use Storm\Contract\Tracker\StreamStory;
use Storm\Contract\Tracker\TransactionalStreamStory;

final class RollbackTransaction
{
    public function __invoke(TransactionalChronicler $chronicler): Closure
    {
        return static function (TransactionalStreamStory|StreamStory $story) use ($chronicler): void {
            try {
                $chronicler->rollbackTransaction();
            } catch (TransactionNotStarted $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
