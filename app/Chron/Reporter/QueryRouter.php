<?php

namespace App\Chron\Reporter;

use App\Chron\Domain\Query\GetOneRandomCustomer;
use App\Chron\Domain\Query\GetOneRandomCustomerHandler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Storm\Contract\Reporter\Router;

final class QueryRouter implements Router
{
    private array $routes = [
        GetOneRandomCustomer::class => GetOneRandomCustomerHandler::class,
    ];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $name): ?array
    {
        if (! array_key_exists($name, $this->routes)) {
            return null;
        }

        $messageHandlers = Arr::wrap($this->routes[$name]);

        return array_map(function ($messageHandler): callable {
            if (is_string($messageHandler)) {
                return $this->container[$messageHandler];
            }

            return $messageHandler;
        }, $messageHandlers);
    }
}
