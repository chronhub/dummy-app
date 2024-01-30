<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Messaging\MessageHandler;
use Illuminate\Container\RewindableGenerator;
use RuntimeException;

use function iterator_to_array;

class MessageServiceLocator
{
    public function __construct(protected KernelStorage $container)
    {
    }

    public function get(string $reporterId, string $messageName): ?array
    {
        $messageHandlers = $this->container->findMessage($messageName);

        if ($messageHandlers instanceof RewindableGenerator) {
            /** @var array<MessageHandler> $messageHandlers */
            $messageHandlers = iterator_to_array($messageHandlers);

            // todo queue handling wip
            foreach ($messageHandlers as $messageHandler) {
                if ($messageHandler->reporterId() !== $reporterId) {
                    throw new RuntimeException('Message found but dispatch in a wrong reporter');
                }
            }

            return $messageHandlers;
        }

        return null;
    }
}
