<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use Storm\Contract\Tracker\Listener;

use function fnmatch;

class SubscriberHandler
{
    public function __construct(
        public string $name,
        public array $reporters,
        public Listener $listener,
        public bool $autowire,
    ) {
    }

    public function match(string $name): bool
    {
        foreach ($this->reporters as $reporter) {
            if (fnmatch($reporter, $name) && $this->autowire) {
                return true;
            }
        }

        return false;
    }
}
