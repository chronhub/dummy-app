<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter\Decorator;

use Storm\Contract\Message\MessageDecorator;
use Storm\Message\Message;

final class ChainMessageDecorator implements MessageDecorator
{
    private array $messageDecorators;

    public function __construct(MessageDecorator ...$messageDecorators)
    {
        $this->messageDecorators = $messageDecorators;
    }

    public function decorate(Message $message): Message
    {
        foreach ($this->messageDecorators as $messageDecorator) {
            $message = $messageDecorator->decorate($message);
        }

        return $message;
    }
}
