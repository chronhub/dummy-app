<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

class ReporterAttribute
{
    public function __construct(
        public string $id,
        public string $abstract,
        public string $type,
        public string $enqueue,
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
            'class' => $this->abstract,
            'type' => $this->type,
            'enqueue' => $this->enqueue,
            'subscribers' => $this->subscribers,
            'listeners' => $this->listeners,
            'queue' => $this->defaultQueue,
            'tracker' => $this->tracker,
        ];
    }
}
