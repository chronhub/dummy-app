<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Application\Messaging\Event\Customer\WhenCustomerEmailChanged;
use App\Chron\Application\Messaging\Event\Customer\WhenCustomerRegistered;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemAdded;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemRefilled;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemReserved;
use App\Chron\Application\Messaging\Event\Order\WhenOrderCanceled;
use App\Chron\Application\Messaging\Event\Order\WhenOrderClosed;
use App\Chron\Application\Messaging\Event\Order\WhenOrderCreated;
use App\Chron\Application\Messaging\Event\Order\WhenOrderDelivered;
use App\Chron\Application\Messaging\Event\Order\WhenOrderModified;
use App\Chron\Application\Messaging\Event\Order\WhenOrderPaid;
use App\Chron\Application\Messaging\Event\Order\WhenOrderRefunded;
use App\Chron\Application\Messaging\Event\Order\WhenOrderReturned;
use App\Chron\Application\Messaging\Event\Order\WhenOrderShipped;
use App\Chron\Application\Messaging\Event\Product\WhenProductCreated;
use App\Chron\Infrastructure\Repository\CustomerAggregateRepository;
use App\Chron\Infrastructure\Repository\InventoryAggregateRepository;
use App\Chron\Infrastructure\Repository\OrderAggregateRepository;
use App\Chron\Infrastructure\Repository\ProductAggregateRepository;
use App\Chron\Model\Customer\Handler\ChangeCustomerEmailHandler;
use App\Chron\Model\Customer\Handler\RegisterCustomerHandler;
use App\Chron\Model\Inventory\Handler\AddInventoryItemHandler;
use App\Chron\Model\Inventory\Handler\RefillInventoryItemHandler;
use App\Chron\Model\Inventory\Handler\ReserveInventoryItemHandler;
use App\Chron\Model\Order\Handler\AddOrderItemHandler;
use App\Chron\Model\Order\Handler\CancelOrderHandler;
use App\Chron\Model\Order\Handler\CloseOrderHandler;
use App\Chron\Model\Order\Handler\CreateOrderHandler;
use App\Chron\Model\Order\Handler\DeliverOrderHandler;
use App\Chron\Model\Order\Handler\ModifyOrderHandler;
use App\Chron\Model\Order\Handler\PayOrderHandler;
use App\Chron\Model\Order\Handler\QueryRandomPendingOrderHandler;
use App\Chron\Model\Order\Handler\RefundOrderHandler;
use App\Chron\Model\Order\Handler\ReturnOrderHandler;
use App\Chron\Model\Order\Handler\ShipOrderHandler;
use App\Chron\Model\Product\Handler\CreateProductHandler;
use App\Chron\Package\Chronicler\PgsqlTransactionalChronicler;
use App\Chron\Package\Chronicler\Subscribers\AppendOnlyStream;
use App\Chron\Package\Chronicler\Subscribers\BeginTransaction;
use App\Chron\Package\Chronicler\Subscribers\CommitTransaction;
use App\Chron\Package\Chronicler\Subscribers\DeleteStream;
use App\Chron\Package\Chronicler\Subscribers\FilterCategories;
use App\Chron\Package\Chronicler\Subscribers\FilterStreams;
use App\Chron\Package\Chronicler\Subscribers\RetrieveAllBackwardStream;
use App\Chron\Package\Chronicler\Subscribers\RetrieveAllStream;
use App\Chron\Package\Chronicler\Subscribers\RetrieveFilteredStream;
use App\Chron\Package\Chronicler\Subscribers\RollbackTransaction;
use App\Chron\Package\Chronicler\Subscribers\StreamExists;
use App\Chron\Package\EventPublisher\EventPublisherSubscriber;
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
        ModifyOrderHandler::class,
        CancelOrderHandler::class,
        PayOrderHandler::class,
        ShipOrderHandler::class,
        DeliverOrderHandler::class,
        ReturnOrderHandler::class,
        RefundOrderHandler::class,
        CloseOrderHandler::class,

        //
        CreateProductHandler::class,
        AddInventoryItemHandler::class,
        RefillInventoryItemHandler::class,
        ReserveInventoryItemHandler::class,
        AddOrderItemHandler::class,

        // event handlers
        WhenCustomerRegistered::class,
        WhenCustomerEmailChanged::class,
        WhenOrderCreated::class,
        WhenOrderModified::class,
        WhenOrderPaid::class,
        WhenOrderCanceled::class,
        WhenOrderShipped::class,
        WhenOrderDelivered::class,
        WhenOrderReturned::class,
        WhenOrderRefunded::class,
        WhenOrderClosed::class,

        //
        WhenProductCreated::class,
        WhenInventoryItemAdded::class,
        WhenInventoryItemRefilled::class,
        WhenInventoryItemReserved::class,

        // query handlers
        QueryRandomPendingOrderHandler::class,
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

    /**
     * @var array<class-string>
     */
    protected array $streamSubscribers = [
        AppendOnlyStream::class,
        DeleteStream::class,
        FilterCategories::class,
        FilterStreams::class,
        RetrieveAllStream::class,
        RetrieveAllBackwardStream::class,
        RetrieveFilteredStream::class,
        StreamExists::class,
        BeginTransaction::class,
        CommitTransaction::class,
        RollbackTransaction::class,

        EventPublisherSubscriber::class,
        CorrelationHeaderCommand::class,
    ];

    protected array $chroniclers = [
        PgsqlTransactionalChronicler::class,
    ];

    protected array $aggregateRepositories = [
        CustomerAggregateRepository::class,
        OrderAggregateRepository::class,
        InventoryAggregateRepository::class,
        ProductAggregateRepository::class,
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

    public function getChroniclerClasses(): Collection
    {
        return collect($this->chroniclers);
    }

    public function getStreamSubscriberClasses(): Collection
    {
        return collect($this->streamSubscribers);
    }

    public function getAggregateRepositoryClasses(): Collection
    {
        return collect($this->aggregateRepositories);
    }
}
