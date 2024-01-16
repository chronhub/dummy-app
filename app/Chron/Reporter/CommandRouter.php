<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Domain\Command\MakeOrder;
use App\Chron\Domain\Command\MakeOrderHandler;
use App\Chron\Domain\Command\RegisterCustomer;
use App\Chron\Domain\Command\RegisterCustomerHandler;
use App\Chron\Domain\Command\UpdateCustomerEmail;
use App\Chron\Domain\Command\UpdateCustomerEmailHandler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Storm\Contract\Reporter\Router;

use function array_key_exists;
use function array_map;
use function is_string;

final class CommandRouter implements Router
{
    private array $routes = [
        RegisterCustomer::class => RegisterCustomerHandler::class,
        UpdateCustomerEmail::class => UpdateCustomerEmailHandler::class,
        MakeOrder::class => MakeOrderHandler::class,
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
