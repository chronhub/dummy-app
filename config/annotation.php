<?php

declare(strict_types=1);

return [
    'reporters' => [
        \Storm\Reporter\ReportCommand::class,
        \Storm\Reporter\ReportEvent::class,
        \Storm\Reporter\ReportQuery::class,
    ],

    'reporter_subscribers' => [
        \Storm\Reporter\Subscriber\MakeMessage::class,
        \Storm\Reporter\Subscriber\MessageDecorators::class,
        \Storm\Reporter\Subscriber\RouteMessage::class,
        \Storm\Reporter\Subscriber\QueryRouteMessage::class,
        \Storm\Reporter\Subscriber\HandleCommand::class,
        \Storm\Reporter\Subscriber\HandleEvent::class,
        \Storm\Reporter\Subscriber\HandleQuery::class,
        \Storm\Reporter\Subscriber\TransactionalCommand::class,
        \Storm\Reporter\Subscriber\CorrelationHeaderCommand::class,
    ],

    'message_handlers' => [
        // command handlers
        \App\Chron\Model\Customer\Handler\RegisterCustomerHandler::class,
        \App\Chron\Model\Customer\Handler\ChangeCustomerEmailHandler::class,

        // cart
        \App\Chron\Model\Cart\Handler\OpenCartHandler::class,
        \App\Chron\Model\Cart\Handler\AddCartItemHandler::class,
        \App\Chron\Model\Cart\Handler\RemoveCartItemHandler::class,
        \App\Chron\Model\Cart\Handler\UpdateCartItemHandler::class,
        \App\Chron\Model\Cart\Handler\CancelCartHandler::class,
        \App\Chron\Model\Cart\Handler\CheckoutCartHandler::class,

        // product
        \App\Chron\Model\Product\Handler\CreateProductHandler::class,

        // inventory
        \App\Chron\Model\Inventory\Handler\AddInventoryItemHandler::class,
        \App\Chron\Model\Inventory\Handler\RefillInventoryItemHandler::class,
        \App\Chron\Model\Inventory\Handler\ReserveInventoryItemHandler::class,
        \App\Chron\Model\Inventory\Handler\AdjustInventoryItemHandler::class,

        // order
        \App\Chron\Model\Order\Handler\CustomerRequestsOrderCancellationHandler::class,
        \App\Chron\Model\Order\Handler\PayOrderHandler::class,

        // event handlers
        \App\Chron\Application\Messaging\Event\Customer\WhenCustomerRegistered::class,
        \App\Chron\Application\Messaging\Event\Customer\WhenCustomerEmailChanged::class,

        //
        \App\Chron\Application\Messaging\Event\Product\WhenProductCreated::class,

        //
        \App\Chron\Application\Messaging\Event\Cart\WhenCartOpened::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartItemAdded::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartItemRemoved::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartItemQuantityUpdated::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartCanceled::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartCheckout::class,

        \App\Chron\Application\Messaging\Event\Order\WhenOrderCreated::class,
        \App\Chron\Application\Messaging\Event\Order\WhenOrderPaid::class,
        \App\Chron\Application\Messaging\Event\Cart\WhenCartItemPartiallyAdded::class,

        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemAdded::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemRefilled::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemReserved::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemPartiallyReserved::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemReleased::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemAdjusted::class,
        \App\Chron\Application\Messaging\Event\Inventory\WhenInventoryItemExhausted::class,

        // query handlers
        \App\Chron\Model\Customer\Handler\QueryCustomerProfileHandler::class,
        \App\Chron\Model\Order\Handler\QueryOrderOfCustomerHandler::class,
        \App\Chron\Model\Order\Handler\QueryOrdersSummaryOfCustomerHandler::class,
        \App\Chron\Model\Customer\Handler\QueryPaginatedCustomersHandler::class,
        \App\Chron\Model\Cart\Handler\QueryCartHistoryHandler::class,
        \App\Chron\Model\Cart\Handler\QueryOpenedCartByCustomerIdHandler::class,
        \App\Chron\Model\Inventory\Handler\QueryFirstTenInventoryItemsHandler::class,
        \App\Chron\Model\Product\Handler\QueryPaginatedProductsHandler::class,
        \App\Chron\Model\Customer\Handler\QueryRandomCustomerHandler::class,
        \App\Chron\Model\Inventory\Handler\QueryInventoryBySkuHandler::class,
        \App\Chron\Model\Customer\Handler\QueryAllNonEmptyOpenedCartsHandler::class,
        \App\Chron\Model\Inventory\Handler\QueryRandomProductInventoryHandler::class,
        \App\Chron\Model\Cart\Handler\QueryAllSubmittedCartHandler::class,
        \App\Chron\Model\Order\Handler\QueryOpenOrderOfCustomerHandler::class,
        \App\Chron\Model\Catalog\QueryProductFromCatalogHandler::class,
        \App\Chron\Model\Catalog\QueryRandomAvailableProductFromCatalogHandler::class,
        \App\Chron\Model\Cart\Handler\QueryRandomOpenCartHandler::class,
    ],

    'chroniclers' => [
        \Storm\Chronicler\Connection\PgsqlTransactionalChronicler::class,
    ],

    'stream_subscribers' => [
        \Storm\Chronicler\Subscriber\AppendOnlyStream::class,
        \Storm\Chronicler\Subscriber\DeleteStream::class,
        \Storm\Chronicler\Subscriber\FilterCategories::class,
        \Storm\Chronicler\Subscriber\FilterStreams::class,
        \Storm\Chronicler\Subscriber\BeginTransaction::class,
        \Storm\Chronicler\Subscriber\CommitTransaction::class,
        \Storm\Chronicler\Subscriber\RollbackTransaction::class,
        \Storm\Chronicler\Subscriber\RetrieveAllStream::class,
        \Storm\Chronicler\Subscriber\RetrieveAllBackwardStream::class,
        \Storm\Chronicler\Subscriber\RetrieveFilteredStream::class,
        \Storm\Chronicler\Subscriber\StreamExists::class,
        \Storm\Chronicler\Publisher\EventPublisherSubscriber::class,
        \Storm\Reporter\Subscriber\CorrelationHeaderCommand::class,
    ],

    'aggregate_repositories' => [
        \App\Chron\Infrastructure\Repository\CustomerAggregateRepository::class,
        \App\Chron\Infrastructure\Repository\OrderAggregateRepository::class,
        \App\Chron\Infrastructure\Repository\InventoryAggregateRepository::class,
        \App\Chron\Infrastructure\Repository\ProductAggregateRepository::class,
        \App\Chron\Infrastructure\Repository\CartAggregateRepository::class,
    ],
];
