<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use JsonSerializable;

class MessageHandlerAttribute implements JsonSerializable
{
    public function __construct(
        public string $reporterId,
        public string $handlerClass,
        public string $handlerMethod,
        public string $handles,
        public null|string|array $queue,
        public int $priority,
        public string $type,
        public array $references,
        public ?string $handlerId = null,
        public ?string $messageId = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'reporter_id' => $this->reporterId,
            'handler_class' => $this->handlerClass,
            'handler_method' => $this->handlerMethod,
            'handles' => $this->handles,
            'queue' => $this->queue,
            'priority' => $this->priority,
            'type' => $this->type,
            'references' => $this->references,
            'handler_id' => $this->handlerId,
            'message_id' => $this->messageId,
        ];
    }

    public function newInstance(string $handlerId, string $messageId, ?array $queue): self
    {
        $self = clone $this;

        return new $self(
            $this->reporterId,
            $this->handlerClass,
            $this->handlerMethod,
            $this->handles,
            $queue,
            $this->priority,
            $this->type,
            $this->references,
            $handlerId,
            $messageId,
        );
    }
}
