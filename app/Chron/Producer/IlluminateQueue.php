<?php

declare(strict_types=1);

namespace App\Chron\Producer;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Storm\Contract\Message\Header;
use Storm\Contract\Serializer\MessageSerializer;
use Storm\Message\Message;

final readonly class IlluminateQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private MessageSerializer $messageSerializer
    ) {
    }

    public function toQueue(Message $message): void
    {
        $queueMessage = $this->detectQueue($message);

        $payload = $this->messageSerializer->serializeMessage($queueMessage);

        $this->dispatcher->dispatchToQueue(new MessageJob($payload->jsonSerialize()));
    }

    private function detectQueue(Message $message): Message
    {
        if ($message->hasNot(Header::QUEUE)) {
            $message = $message->withHeader(Header::QUEUE, 'default');
        }

        return $message;
    }
}
