<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

class ReporterAttribute
{
    public function __construct(
        public string $id,
        public string $class,
        public string $type,
        public bool $sync,
        public string|array $subscribers,
        public array $listeners,
        public ?string $defaultQueue,
        public ?string $tracker,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'class' => $this->class,
            'type' => $this->type,
            'sync' => $this->sync,
            'subscribers' => $this->subscribers,
            'listeners' => $this->listeners,
            'queue' => $this->defaultQueue,
            'tracker' => $this->tracker,
        ];
    }
}
