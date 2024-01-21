<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Storm\Contract\Serializer\MessageSerializer;
use Storm\Message\Message;

class IlluminateQueue
{
    public function __construct(
        protected QueueingDispatcher $dispatcher,
        protected MessageSerializer $messageSerializer
    ) {
    }

    public function toQueue(Message $message, ?array $currentQueue): void
    {
        $payload = $this->messageSerializer->serializeMessage($message);

        $messageJob = new MessageJob($payload->jsonSerialize(), $currentQueue);

        logger('dispatching to queue', [
            'message' => $message->name(),
        ]);
        $this->dispatcher->dispatchToQueue($messageJob);
    }
}
