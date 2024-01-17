<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Domain\Command\MakeOrderHandler;
use App\Chron\Domain\Command\RegisterCustomerHandler;
use App\Chron\Domain\Command\UpdateCustomerEmailHandler;
use App\Chron\Domain\Event\OnEvent\SendEmailToRegisteredCustomer;
use App\Chron\Domain\Event\OnEvent\WhenCustomerEmailUpdated;
use App\Chron\Domain\Event\OnEvent\WhenCustomerRegistered;
use App\Chron\Domain\Event\OnEvent\WhenOrderMade;

class ClassMap
{
    public array $classes = [
        MakeOrderHandler::class,
        RegisterCustomerHandler::class,
        UpdateCustomerEmailHandler::class,

        WhenCustomerRegistered::class,
        SendEmailToRegisteredCustomer::class,
        WhenCustomerEmailUpdated::class,

        WhenOrderMade::class,
    ];
}
