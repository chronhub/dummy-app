<?php

declare(strict_types=1);

namespace App\Chron\Model\Catalog;

use App\Chron\Application\Messaging\Command\Catalog\QueryProductFromCatalog;
use App\Chron\Projection\Provider\CatalogProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryProductFromCatalog::class,
)]
final readonly class QueryProductFromCatalogHandler
{
    public function __construct(private CatalogProvider $catalogProvider)
    {

    }

    public function __invoke(QueryProductFromCatalog $query, Deferred $promise): void
    {
        $product = $this->catalogProvider->findAvailableProductById($query->productId);

        $promise->resolve($product);
    }
}
