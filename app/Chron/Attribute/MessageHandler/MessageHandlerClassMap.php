<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

use App\Chron\Domain\Command\MakeOrderHandler;
use App\Chron\Domain\Command\RegisterCustomerHandler;
use App\Chron\Domain\Command\UpdateCustomerEmailHandler;
use App\Chron\Domain\Event\OnEvent\SendEmailToRegisteredCustomer;
use App\Chron\Domain\Event\OnEvent\WhenCustomerEmailUpdated;
use App\Chron\Domain\Event\OnEvent\WhenCustomerRegistered;
use App\Chron\Domain\Event\OnEvent\WhenOrderMade;
use App\Chron\Domain\Query\GetOneRandomCustomerHandler;

class MessageHandlerClassMap
{
    public array $classes = [
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
}
