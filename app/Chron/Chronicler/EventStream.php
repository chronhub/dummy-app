<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

final readonly class EventStream
{
    public function __construct(
        private string $streamName,
        private string $tableName,
        private ?string $category = null
    ) {
    }

    public function realStreamName(): string
    {
        return $this->streamName;
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function jsonSerialize(): array
    {
        return [
            'real_stream_name' => $this->streamName,
            'stream_name' => $this->tableName,
            'category' => $this->category,
        ];
    }
}
