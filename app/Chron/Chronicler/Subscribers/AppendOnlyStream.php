<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Subscribers;

use App\Chron\Chronicler\Contracts\Chronicler;
use Closure;
use Storm\Chronicler\Exceptions\ConcurrencyException;
use Storm\Chronicler\Exceptions\StreamAlreadyExists;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Tracker\StreamStory;

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
