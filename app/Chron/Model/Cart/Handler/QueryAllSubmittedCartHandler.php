<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Query\QueryAllSubmittedCart;
use App\Chron\Projection\Provider\CartProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryAllSubmittedCart::class,
)]
final readonly class QueryAllSubmittedCartHandler
{
    public function __construct(private CartProvider $cartProvider)
    {
    }

    public function __invoke(QueryAllSubmittedCart $query, Deferred $promise): void
    {
        $carts = $this->cartProvider->findAllSubmittedCart();

        $promise->resolve($carts);
    }
}
