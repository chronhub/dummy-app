<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Subscribers;

use App\Chron\Chronicler\Contracts\Chronicler;
use Closure;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Tracker\StreamStory;
use Storm\Stream\Stream;

final class RetrieveFilteredStream
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story) use ($chronicler): void {
            try {
                [$streamName, $queryFilter] = $story->promise();

                $streamEvents = $chronicler->retrieveFiltered($streamName, $queryFilter);

                $newStream = new Stream($streamName, $streamEvents);

                $story->deferred(static fn (): Stream => $newStream);
            } catch (StreamNotFound $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
