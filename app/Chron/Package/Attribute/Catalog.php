<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Application\Messaging\Event\Customer\SendEmailToRegisteredCustomer;
use App\Chron\Application\Messaging\Event\Customer\WhenCustomerEmailChanged;
use App\Chron\Application\Messaging\Event\Customer\WhenCustomerRegistered;
use App\Chron\Application\Messaging\Event\Order\WhenOrderCompleted;
use App\Chron\Application\Messaging\Event\Order\WhenOrderCreated;
use App\Chron\Model\Customer\Handler\ChangeCustomerEmailHandler;
use App\Chron\Model\Customer\Handler\RegisterCustomerHandler;
use App\Chron\Model\Order\Handler\CompleteOrderHandler;
use App\Chron\Model\Order\Handler\CreateOrderHandler;
use App\Chron\Package\Reporter\ReportCommand;
use App\Chron\Package\Reporter\ReportEvent;
use App\Chron\Package\Reporter\ReportNotification;
use App\Chron\Package\Reporter\ReportQuery;
use App\Chron\Package\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Package\Reporter\Subscribers\HandleCommand;
use App\Chron\Package\Reporter\Subscribers\HandleEvent;
use App\Chron\Package\Reporter\Subscribers\HandleQuery;
use App\Chron\Package\Reporter\Subscribers\MakeMessage;
use App\Chron\Package\Reporter\Subscribers\MessageDecorators;
use App\Chron\Package\Reporter\Subscribers\QueryRouteMessage;
use App\Chron\Package\Reporter\Subscribers\RouteMessage;
use App\Chron\Package\Reporter\Subscribers\TransactionalCommand;
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
        RegisterCustomerHandler::class,
        ChangeCustomerEmailHandler::class,
        CreateOrderHandler::class,
        CompleteOrderHandler::class,

        // event handlers
        WhenCustomerRegistered::class,
        SendEmailToRegisteredCustomer::class,
        WhenCustomerEmailChanged::class,
        WhenOrderCreated::class,
        WhenOrderCompleted::class,
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
