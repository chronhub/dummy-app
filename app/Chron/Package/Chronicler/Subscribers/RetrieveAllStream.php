<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\Chronicler;
use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use App\Chron\Package\Chronicler\Direction;
use Closure;
use InvalidArgumentException;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Tracker\StreamStory;
use Storm\Stream\Stream;

#[AsStreamSubscriber(
    event: EventableChronicler::ALL_STREAM_EVENT,
    chronicler: 'chronicler.event.*'
)]
final class RetrieveAllStream
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story) use ($chronicler): void {
            try {
                [$streamName, $aggregateId, $direction] = $story->promise();

                if ($direction !== Direction::FORWARD) {
                    throw new InvalidArgumentException('Direction must be forward');
                }

                $streamEvents = $chronicler->retrieveAll($streamName, $aggregateId, $direction);

                $newStream = new Stream($streamName, $streamEvents);

                $story->deferred(static fn (): Stream => $newStream);
            } catch (StreamNotFound $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
