<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use Closure;
use Storm\Chronicler\Exceptions\TransactionAlreadyStarted;
use Storm\Contract\Tracker\StreamStory;
use Storm\Contract\Tracker\TransactionalStreamStory;

final class BeginTransaction
{
    public function __invoke(TransactionalChronicler $chronicler): Closure
    {
        return static function (TransactionalStreamStory|StreamStory $story) use ($chronicler): void {
            try {
                $chronicler->beginTransaction();
            } catch (TransactionAlreadyStarted $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
