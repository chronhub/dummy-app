<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Domain\Command\MakeOrderHandler;
use App\Chron\Domain\Command\RegisterCustomerHandler;
use App\Chron\Domain\Command\SomeNakedCommandHandler;
use App\Chron\Domain\Command\UpdateCustomerEmailHandler;
use App\Chron\Domain\Event\OnEvent\SendEmailToRegisteredCustomer;
use App\Chron\Domain\Event\OnEvent\WhenCustomerEmailUpdated;
use App\Chron\Domain\Event\OnEvent\WhenCustomerRegistered;
use App\Chron\Domain\Event\OnEvent\WhenOrderMade;
use App\Chron\Domain\Query\GetOneRandomCustomerHandler;
use App\Chron\Reporter\ReportCommand;
use App\Chron\Reporter\ReportEvent;
use App\Chron\Reporter\ReportNotification;
use App\Chron\Reporter\ReportQuery;
use App\Chron\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Reporter\Subscribers\HandleCommand;
use App\Chron\Reporter\Subscribers\HandleEvent;
use App\Chron\Reporter\Subscribers\HandleQuery;
use App\Chron\Reporter\Subscribers\MakeMessage;
use App\Chron\Reporter\Subscribers\MessageDecorators;
use App\Chron\Reporter\Subscribers\QueryRouteMessage;
use App\Chron\Reporter\Subscribers\RouteMessage;
use App\Chron\Reporter\Subscribers\TransactionalCommand;
use Illuminate\Support\Collection;

class Catalog
{
    /**
     * @var array<class-string>
     */
    protected array $reporters = [
        ReportCommand::class,
        ReportNotification::class,
        ReportEvent::class,
        ReportQuery::class,
    ];

    /**
     * @var array<class-string>
     */
    protected array $messageHandlers = [
        // command handlers
        MakeOrderHandler::class,
        RegisterCustomerHandler::class,
        UpdateCustomerEmailHandler::class,
        SomeNakedCommandHandler::class,

        // event handlers
        WhenCustomerRegistered::class,
        SendEmailToRegisteredCustomer::class,
        WhenCustomerEmailUpdated::class,
        WhenOrderMade::class,

        // query handlers
        GetOneRandomCustomerHandler::class,
    ];

    /**
     * @var array<class-string>
     */
    protected array $subscribers = [
        MakeMessage::class,
        MessageDecorators::class,
        RouteMessage::class,
        QueryRouteMessage::class,
        HandleCommand::class,
        HandleEvent::class,
        HandleQuery::class,
        TransactionalCommand::class,
        CorrelationHeaderCommand::class,
    ];

    public function find(): iterable
    {
        // todo auto discovery
        return [];
    }

    public function getReporterClasses(): Collection
    {
        return collect($this->reporters);
    }

    public function getMessageHandlersClasses(): Collection
    {
        return collect($this->messageHandlers);
    }

    public function getSubscriberClasses(): Collection
    {
        return collect($this->subscribers);
    }
}
