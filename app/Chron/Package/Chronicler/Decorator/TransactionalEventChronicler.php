<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Decorator;

use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use LogicException;
use Storm\Contract\Tracker\TransactionalStreamStory;
use Throwable;

final readonly class TransactionalEventChronicler extends EventChronicler implements TransactionalEventableChronicler
{
    public function beginTransaction(): void
    {
        /** @var TransactionalStreamStory $story */
        $story = $this->streamTracker->newStory(self::BEGIN_TRANSACTION_EVENT);

        $this->streamTracker->disclose($story);

        if ($story->hasTransactionAlreadyStarted()) {
            throw $story->exception();
        }
    }

    public function commitTransaction(): void
    {
        $story = $this->streamTracker->newStory(self::COMMIT_TRANSACTION_EVENT);

        $this->streamTracker->disclose($story);

        /** @var TransactionalStreamStory $story */
        if ($story->hasTransactionNotStarted()) {
            throw $story->exception();
        }
    }

    public function rollbackTransaction(): void
    {
        $story = $this->streamTracker->newStory(self::ROLLBACK_TRANSACTION_EVENT);

        $this->streamTracker->disclose($story);

        /** @var TransactionalStreamStory $story */
        if ($story->hasTransactionNotStarted()) {
            throw $story->exception();
        }
    }

    public function transactional(callable $callback): bool|array|string|int|float|object
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);

            $this->commitTransaction();

            return $result;
        } catch (Throwable $exception) {
            $this->rollbackTransaction();

            throw $exception;
        }
    }

    public function inTransaction(): bool
    {
        if (! $this->innerChronicler() instanceof TransactionalChronicler) {
            throw new LogicException('Inner chronicler is not a transactional chronicler');
        }

        return $this->innerChronicler()->inTransaction();
    }
}
