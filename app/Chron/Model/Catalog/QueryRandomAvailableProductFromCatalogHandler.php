<?php

declare(strict_types=1);

namespace App\Chron\Model\Catalog;

use App\Chron\Application\Messaging\Command\Catalog\QueryRandomAvailableProductFromCatalog;
use App\Chron\Projection\Provider\CatalogProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomAvailableProductFromCatalog::class,
)]
final readonly class QueryRandomAvailableProductFromCatalogHandler
{
    public function __construct(private CatalogProvider $catalogProvider)
    {
    }

    public function __invoke(QueryRandomAvailableProductFromCatalog $query, Deferred $promise): void
    {
        $product = $this->catalogProvider->findRandomAvailableProduct();

        $promise->resolve($product);
    }
}
