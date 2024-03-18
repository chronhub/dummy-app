<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Handler;

use App\Chron\Application\Messaging\Query\QueryPaginatedProducts;
use App\Chron\Projection\Provider\ProductProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryPaginatedProducts::class
)]
final readonly class QueryPaginatedProductsHandler
{
    public function __construct(private ProductProvider $productProvider)
    {
    }

    public function __invoke(QueryPaginatedProducts $query, Deferred $promise): void
    {
        $result = $this->productProvider->getPaginatedProducts($query->limit);

        $promise->resolve($result);
    }
}
