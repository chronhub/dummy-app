<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Router;

use App\Chron\Attribute\TagHandlerContainer;
use Storm\Contract\Reporter\Router;

use function iterator_to_array;

final readonly class MessageRouter implements Router
{
    public function __construct(private TagHandlerContainer $container)
    {
    }

    public function get(string $name): ?array
    {
        $messageHandlers = iterator_to_array($this->container->find($name));

        return $messageHandlers === [] ? null : $messageHandlers;
    }
}
