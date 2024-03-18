<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Query\QueryCartHistory;
use App\Chron\Model\Cart\Repository\CartList;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryCartHistory::class,
)]
final readonly class QueryCartHistoryHandler
{
    public function __construct(private CartList $cartList)
    {
    }

    public function __invoke(QueryCartHistory $query, Deferred $promise): void
    {
        // todo pagination cursor, $query->from(), $query->to()
        $cartHistory = $this->cartList->history($query->cartId());

        $promise->resolve($cartHistory);
    }
}
