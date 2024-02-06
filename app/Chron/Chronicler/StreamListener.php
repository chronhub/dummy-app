<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use Closure;
use ReflectionFunction;
use Storm\Contract\Tracker\Listener;

final class StreamListener implements Listener
{
    private string $event;

    /**
     * @var callable
     */
    private $story;

    private int $priority;

    public function __construct(string $event, callable $callback, int $priority = 0)
    {
        $this->event = $event;
        $this->story = $callback;
        $this->priority = $priority;
    }

    public function name(): string
    {
        return $this->event;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function story(): callable
    {
        return $this->story;
    }

    public function origin(): string
    {
        if ($this->story instanceof Closure) {
            $origin = new ReflectionFunction($this->story);

            return $origin->getClosureScopeClass()->name;
        }

        return $this->story::class;
    }
}
