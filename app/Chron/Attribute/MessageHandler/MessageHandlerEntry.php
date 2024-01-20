<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Attribute\MessageHandlerData;
use JsonSerializable;

class MessageHandlerEntry implements JsonSerializable
{
    public function __construct(
        public string $messageId,
        public string $messageName,
        public string $messageHandlerId,
        public MessageHandlerData $data,
        public ?object $queue
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'reporter_id' => $this->data->reporterId,
            'message_name' => $this->messageName,
            'message_id' => $this->messageId,
            'message_handler' => [
                'id' => $this->messageHandlerId,
                'class' => $this->data->reflectionClass->getName(),
                'method' => $this->data->handlerMethod,
                'type' => $this->data->type->value,
                'priority' => $this->data->priority,
                'queue' => $this->queue?->jsonSerialize(),
            ],
        ];
    }
}
