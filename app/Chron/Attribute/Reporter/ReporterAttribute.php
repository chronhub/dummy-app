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
        public array $listeners,
        public ?string $defaultQueue,
        public ?string $tracker,
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     class: string,
     *     type: string,
     *     enqueue: string,
     *     listeners: array<string>,
     *     queue: null|string,
     *     tracker: null|string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'class' => $this->abstract,
            'type' => $this->type,
            'enqueue' => $this->enqueue,
            'listeners' => $this->listeners,
            'queue' => $this->defaultQueue,
            'tracker' => $this->tracker,
        ];
    }
}
