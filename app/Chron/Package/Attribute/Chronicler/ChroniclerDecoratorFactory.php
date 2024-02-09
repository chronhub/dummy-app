<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Chronicler;

use App\Chron\Package\Chronicler\Contracts\Chronicler;
use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use App\Chron\Package\Chronicler\Decorator\EventChronicler;
use App\Chron\Package\Chronicler\Decorator\TransactionalEventChronicler;
use Storm\Contract\Tracker\StreamTracker;
use Storm\Contract\Tracker\TransactionalStreamTracker;

class ChroniclerDecoratorFactory
{
    public function makeEventableChronicler(Chronicler $realInstance, StreamTracker $streamTracker): EventableChronicler
    {
        return new EventChronicler($realInstance, $streamTracker);
    }

    public function makeTransactionalEventableChronicler(Chronicler $realInstance, TransactionalStreamTracker $streamTracker): TransactionalEventableChronicler
    {
        return new TransactionalEventChronicler($realInstance, $streamTracker);
    }
}
