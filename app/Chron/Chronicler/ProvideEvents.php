<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\EventableChronicler;
use App\Chron\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Chronicler\Contracts\TransactionalEventableChronicler;
use App\Chron\Chronicler\Subscribers\AppendOnlyStream;
use App\Chron\Chronicler\Subscribers\BeginTransaction;
use App\Chron\Chronicler\Subscribers\CommitTransaction;
use App\Chron\Chronicler\Subscribers\DeleteStream;
use App\Chron\Chronicler\Subscribers\FilterCategories;
use App\Chron\Chronicler\Subscribers\FilterStreams;
use App\Chron\Chronicler\Subscribers\RetrieveAllBackwardStream;
use App\Chron\Chronicler\Subscribers\RetrieveAllStream;
use App\Chron\Chronicler\Subscribers\RetrieveFilteredStream;
use App\Chron\Chronicler\Subscribers\RollbackTransaction;
use App\Chron\Chronicler\Subscribers\StreamExists;
use Storm\Contract\Tracker\StreamTracker;
use Storm\Contract\Tracker\TransactionalStreamTracker;

class ProvideEvents
{
    protected static array $events = [
        [EventableChronicler::APPEND_STREAM_EVENT, AppendOnlyStream::class],
        [EventableChronicler::DELETE_STREAM_EVENT, DeleteStream::class],
        [EventableChronicler::FILTER_CATEGORY_EVENT, FilterCategories::class],
        [EventableChronicler::FILTER_STREAM_EVENT, FilterStreams::class],
        [EventableChronicler::ALL_STREAM_EVENT, RetrieveAllStream::class],
        [EventableChronicler::ALL_BACKWARDS_STREAM_EVENT, RetrieveAllBackwardStream::class],
        [EventableChronicler::FILTERED_STREAM_EVENT, RetrieveFilteredStream::class],
        [EventableChronicler::HAS_STREAM_EVENT, StreamExists::class],
    ];

    protected static array $transactionalEvents = [
        [TransactionalEventableChronicler::BEGIN_TRANSACTION_EVENT, BeginTransaction::class],
        [TransactionalEventableChronicler::COMMIT_TRANSACTION_EVENT, CommitTransaction::class],
        [TransactionalEventableChronicler::ROLLBACK_TRANSACTION_EVENT, RollbackTransaction::class],
    ];

    public static function withEvent(Chronicler $chronicler, StreamTracker $tracker): void
    {
        foreach (self::$events as $event) {
            self::subscribe($chronicler, $tracker, $event);
        }

        if ($chronicler instanceof TransactionalChronicler && $tracker instanceof TransactionalStreamTracker) {
            self::withTransactionalEvent($chronicler, $tracker);
        }
    }

    public static function withTransactionalEvent(TransactionalChronicler $chronicler, TransactionalStreamTracker $tracker): void
    {
        foreach (self::$transactionalEvents as $event) {
            self::subscribe($chronicler, $tracker, $event);
        }
    }

    protected static function subscribe(Chronicler $chronicler, TransactionalStreamTracker|StreamTracker $tracker, array $event): void
    {
        [$eventName, $subscriber] = $event;

        $callback = new $subscriber();

        $listener = new StreamListener($eventName, $callback($chronicler), 0);

        $tracker->listen($listener);
    }
}
