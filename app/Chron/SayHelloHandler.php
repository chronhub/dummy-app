<?php

declare(strict_types=1);

namespace App\Chron;

use Storm\Contract\Serializer\MessageSerializer;
use Storm\Message\Attribute\AsCommandHandler;
use Storm\Message\Message;

#[AsCommandHandler(
    reporter: 'reporter.command.sync.default',
    handles: SayHello::class,
)]
class SayHelloHandler
{
    public function __construct(private MessageSerializer $serializer)
    {
    }

    public function __invoke(SayHello $command): void
    {
        logger('Hello World!', ['command' => $this->serializer->serializeMessage(new Message($command))->jsonSerialize()]);
    }
}
