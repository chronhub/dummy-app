<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\QueryRandomOpenCart;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use App\Chron\Projection\Provider\CartProvider;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomOpenCart::class,
)]
final readonly class QueryRandomOpenCartHandler
{
    public function __construct(private CartProvider $cartProvider)
    {
    }

    public function __invoke(QueryRandomOpenCart $query, Deferred $promise): void
    {
        $cart = $this->cartProvider->findRandomOpenedCart();

        $promise->resolve($cart);
    }
}
