<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Subscribers;

use App\Chron\Package\Chronicler\Contracts\Chronicler;
use Closure;
use LogicException;
use Storm\Chronicler\Exceptions\StreamAlreadyExists;
use Storm\Contract\Tracker\StreamStory;

final class FirstCommitStream
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story): void {
            try {
                throw new LogicException('Not implemented');
            } catch (StreamAlreadyExists $exception) {
                $story->withRaisedException($exception);
            }
        };
    }
}
