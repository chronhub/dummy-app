<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Query\QueryOrdersSummaryOfCustomer;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use App\Chron\Projection\Provider\OrderProvider;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryOrdersSummaryOfCustomer::class
)]
final readonly class QueryOrdersSummaryOfCustomerHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function __invoke(QueryOrdersSummaryOfCustomer $query, Deferred $promise): void
    {
        $order = $this->orderProvider->getOrderSummaryOfCustomer($query->customerId()->toString());

        $promise->resolve($order);
    }
}
