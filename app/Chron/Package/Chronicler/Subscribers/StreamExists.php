<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\Chronicler;
use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use Closure;
use Storm\Contract\Tracker\StreamStory;

#[AsStreamSubscriber(
    event: EventableChronicler::HAS_STREAM_EVENT,
    chronicler: 'chronicler.event.*'
)]
final class StreamExists
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story) use ($chronicler): void {
            $streamExists = $chronicler->hasStream($story->promise());

            $story->deferred(static fn (): bool => $streamExists);
        };
    }
}
