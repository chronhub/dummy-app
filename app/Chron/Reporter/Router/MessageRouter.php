<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Router;

use App\Chron\Attribute\MessageServiceLocator;
use Storm\Contract\Reporter\Router;

final readonly class MessageRouter implements Router
{
    public function __construct(private MessageServiceLocator $container)
    {
    }

    public function get(string $name): ?array
    {
        return $this->container->get($name);
    }
}
