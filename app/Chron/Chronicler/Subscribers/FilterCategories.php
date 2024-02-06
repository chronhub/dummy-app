<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Subscribers;

use App\Chron\Chronicler\Contracts\Chronicler;
use Closure;
use Storm\Contract\Tracker\StreamStory;

final class FilterCategories
{
    public function __invoke(Chronicler $chronicler): Closure
    {
        return static function (StreamStory $story) use ($chronicler): void {
            $categories = $chronicler->filterCategories(...$story->promise());

            $story->deferred(static fn (): array => $categories);
        };
    }
}
