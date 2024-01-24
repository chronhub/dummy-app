<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Container\RewindableGenerator;

use function iterator_to_array;

class MessageServiceLocator
{
    public function __construct(protected AttributeContainer $container)
    {
    }

    public function get(string $messageName): ?array
    {
        $messageHandlers = $this->container->get($messageName);

        if ($messageHandlers instanceof RewindableGenerator) {
            return iterator_to_array($messageHandlers);
        }

        return null;
    }
}
