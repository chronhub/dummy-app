<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter\Router;

use App\Chron\Package\Attribute\MessageServiceLocator;
use Storm\Reporter\Exception\MessageNotFound;

final readonly class MessageRouter implements Routable
{
    public function __construct(private MessageServiceLocator $container)
    {
    }

    public function route(string $reporterId, string $message): ?array
    {
        $handlers = $this->container->get($reporterId, $message);

        if ($handlers === null) {
            throw MessageNotFound::withMessageName($message);
        }

        return $handlers;
    }
}
