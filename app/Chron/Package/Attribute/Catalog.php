<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Application\Messaging\Event\Cart\WhenCartCanceled;
use App\Chron\Application\Messaging\Event\Cart\WhenCartCheckout;
use App\Chron\Application\Messaging\Event\Cart\WhenCartItemAdded;
use App\Chron\Application\Messaging\Event\Cart\WhenCartItemPartiallyAdded;
use App\Chron\Application\Messaging\Event\Cart\WhenCartItemQuantityUpdated;
use App\Chron\Application\Messaging\Event\Cart\WhenCartItemRemoved;
use App\Chron\Application\Messaging\Event\Cart\WhenCartOpened;
use App\Chron\Application\Messaging\Event\Customer\WhenCustomerEmailChanged;
use App\Chron\Application\Messaging\Event\Customer\WhenCustomerRegistered;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemAdded;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemAdjusted;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemExhausted;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemPartiallyReserved;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemRefilled;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemReleased;
use App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemReserved;
use App\Chron\Application\Messaging\Event\Order\WhenOrderCreated;
use App\Chron\Application\Messaging\Event\Order\WhenOrderPaid;
use App\Chron\Application\Messaging\Event\Product\WhenProductCreated;
use App\Chron\Infrastructure\Repository\CartAggregateRepository;
use App\Chron\Infrastructure\Repository\CustomerAggregateRepository;
use App\Chron\Infrastructure\Repository\InventoryAggregateRepository;
use App\Chron\Infrastructure\Repository\OrderAggregateRepository;
use App\Chron\Infrastructure\Repository\ProductAggregateRepository;
use App\Chron\Model\Cart\Handler\AddCartItemHandler;
use App\Chron\Model\Cart\Handler\CancelCartHandler;
use App\Chron\Model\Cart\Handler\CheckoutCartHandler;
use App\Chron\Model\Cart\Handler\OpenCartHandler;
use App\Chron\Model\Cart\Handler\QueryCartHistoryHandler;
use App\Chron\Model\Cart\Handler\QueryOpenedCartByCustomerIdHandler;
use App\Chron\Model\Cart\Handler\RemoveCartItemHandler;
use App\Chron\Model\Cart\Handler\UpdateCartItemHandler;
use App\Chron\Model\Customer\Handler\ChangeCustomerEmailHandler;
use App\Chron\Model\Customer\Handler\QueryCustomerProfileHandler;
use App\Chron\Model\Customer\Handler\QueryPaginatedCustomersHandler;
use App\Chron\Model\Customer\Handler\RegisterCustomerHandler;
use App\Chron\Model\Inventory\Handler\AddInventoryItemHandler;
use App\Chron\Model\Inventory\Handler\QueryFirstTenInventoryItemsHandler;
use App\Chron\Model\Inventory\Handler\RefillInventoryItemHandler;
use App\Chron\Model\Inventory\Handler\ReserveInventoryItemHandler;
use App\Chron\Model\Order\Handler\CustomerRequestsOrderCancellationHandler;
use App\Chron\Model\Order\Handler\QueryOrderOfCustomerHandler;
use App\Chron\Model\Order\Handler\QueryOrdersSummaryOfCustomerHandler;
use App\Chron\Model\Product\Handler\CreateProductHandler;
use App\Chron\Model\Product\Handler\QueryPaginatedProductsHandler;
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

        // cart
        OpenCartHandler::class,
        AddCartItemHandler::class,
        RemoveCartItemHandler::class,
        UpdateCartItemHandler::class,
        CancelCartHandler::class,
        CheckoutCartHandler::class,

        // product
        CreateProductHandler::class,

        // inventory
        AddInventoryItemHandler::class,
        RefillInventoryItemHandler::class,
        ReserveInventoryItemHandler::class,

        // order
        CustomerRequestsOrderCancellationHandler::class,

        // event handlers
        WhenCustomerRegistered::class,
        WhenCustomerEmailChanged::class,

        //
        WhenProductCreated::class,

        //
        WhenCartOpened::class,
        WhenCartItemAdded::class,
        WhenCartItemRemoved::class,
        WhenCartItemQuantityUpdated::class,
        WhenCartCanceled::class,
        WhenCartCheckout::class,

        WhenOrderCreated::class,
        WhenOrderPaid::class,
        WhenCartItemPartiallyAdded::class,

        WhenInventoryItemAdded::class,
        WhenInventoryItemRefilled::class,
        WhenInventoryItemReserved::class,
        WhenInventoryItemPartiallyReserved::class,
        WhenInventoryItemReleased::class,
        WhenInventoryItemAdjusted::class,
        WhenInventoryItemExhausted::class,

        // query handlers
        QueryCustomerProfileHandler::class,
        QueryOrderOfCustomerHandler::class,
        QueryOrdersSummaryOfCustomerHandler::class,
        QueryPaginatedCustomersHandler::class,
        QueryCartHistoryHandler::class,
        QueryOpenedCartByCustomerIdHandler::class,
        QueryFirstTenInventoryItemsHandler::class,
        QueryPaginatedProductsHandler::class,
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
        CartAggregateRepository::class,
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
