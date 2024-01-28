<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Messaging;

use App\Chron\Domain\Command\MakeOrderHandler;
use App\Chron\Domain\Command\RegisterCustomerHandler;
use App\Chron\Domain\Command\UpdateCustomerEmailHandler;
use App\Chron\Domain\Event\OnEvent\SendEmailToRegisteredCustomer;
use App\Chron\Domain\Event\OnEvent\WhenCustomerEmailUpdated;
use App\Chron\Domain\Event\OnEvent\WhenCustomerRegistered;
use App\Chron\Domain\Event\OnEvent\WhenOrderMade;
use App\Chron\Domain\Query\GetOneRandomCustomerHandler;
use Illuminate\Support\Collection;

class MessageClassMap
{
    protected array $classes = [
        // command handlers
        MakeOrderHandler::class,
        RegisterCustomerHandler::class,
        UpdateCustomerEmailHandler::class,

        // event handlers
        WhenCustomerRegistered::class,
        SendEmailToRegisteredCustomer::class,
        WhenCustomerEmailUpdated::class,
        WhenOrderMade::class,

        // query handlers
        GetOneRandomCustomerHandler::class,
    ];

    /**
     * @return Collection<class-string>
     */
    public function getClasses(): Collection
    {
        return collect($this->classes);
    }
}
