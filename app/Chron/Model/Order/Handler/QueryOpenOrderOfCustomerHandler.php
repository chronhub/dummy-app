<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Query\QueryOpenOrderOfCustomer;
use App\Chron\Projection\Provider\OrderProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryOpenOrderOfCustomer::class
)]
final readonly class QueryOpenOrderOfCustomerHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function __invoke(QueryOpenOrderOfCustomer $query, Deferred $promise): void
    {
        $order = $this->orderProvider->findOpenOrderOfCustomer($query->orderOwner()->toString());

        $promise->resolve($order);
    }
}
