<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\AggregateRepository;

use App\Chron\Package\Aggregate\AggregateEventReleaser;
use App\Chron\Package\Aggregate\Contract\AggregateRepository;
use App\Chron\Package\Aggregate\GenericAggregateRepository;
use App\Chron\Package\Chronicler\Contracts\Chronicler;
use Storm\Contract\Message\MessageDecorator;
use Storm\Message\NoOpMessageDecorator;
use Storm\Stream\StreamName;

class AggregateRepositoryFactory
{
    public function makeRepository(Chronicler $chronicler, StreamName $streamName, ?MessageDecorator $messageDecorator = null): AggregateRepository
    {
        $messageDecorator ??= new NoOpMessageDecorator();

        $eventReleaser = new AggregateEventReleaser($messageDecorator);

        return new GenericAggregateRepository($chronicler, $streamName, $eventReleaser);
    }
}
