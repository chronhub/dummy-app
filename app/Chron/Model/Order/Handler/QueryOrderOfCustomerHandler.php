<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Query\QueryOrderOfCustomer;
use App\Chron\Projection\Provider\OrderProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryOrderOfCustomer::class
)]
final readonly class QueryOrderOfCustomerHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function __invoke(QueryOrderOfCustomer $query, Deferred $promise): void
    {
        $order = $this->orderProvider->findOrderOfCustomer($query->customerId()->toString(), $query->orderId()->toString());

        $promise->resolve($order);
    }
}
