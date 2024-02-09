<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\Chronicler;
use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use Closure;
use Storm\Chronicler\Exceptions\ConcurrencyException;
use Storm\Chronicler\Exceptions\StreamAlreadyExists;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Tracker\StreamStory;

#[AsStreamSubscriber(
    event: EventableChronicler::APPEND_STREAM_EVENT,
    chronicler: 'chronicler.event.*'
)]
final class AppendOnlyStream
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story) use ($chronicler): void {
            try {
                $chronicler->append($story->promise());
            } catch (StreamAlreadyExists|StreamNotFound|ConcurrencyException $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
