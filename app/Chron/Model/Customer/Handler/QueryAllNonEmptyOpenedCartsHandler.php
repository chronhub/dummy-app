<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Command\Cart\QueryAllNonEmptyOpenedCarts;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use App\Chron\Projection\Provider\CartProvider;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryAllNonEmptyOpenedCarts::class,
)]
final readonly class QueryAllNonEmptyOpenedCartsHandler
{
    public function __construct(private CartProvider $cartProvider)
    {
    }

    public function __invoke(QueryAllNonEmptyOpenedCarts $query, Deferred $promise): void
    {
        $carts = $this->cartProvider->findAllNonEmptyOpenedCarts();

        $promise->resolve($carts);
    }
}
