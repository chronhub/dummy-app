<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Query\QueryOpenedCartByCustomerId;
use App\Chron\Projection\Provider\CartProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryOpenedCartByCustomerId::class,
)]
final readonly class QueryOpenedCartByCustomerIdHandler
{
    public function __construct(private CartProvider $cartProvider)
    {
    }

    public function __invoke(QueryOpenedCartByCustomerId $query, Deferred $promise): void
    {
        $cart = $this->cartProvider->findOpenedCartByCustomerId($query->customerId()->toString());

        $promise->resolve($cart);
    }
}
