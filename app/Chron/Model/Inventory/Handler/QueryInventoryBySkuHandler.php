<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Cart\QueryInventoryBySku;
use App\Chron\Projection\Provider\InventoryProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryInventoryBySku::class,
)]
final readonly class QueryInventoryBySkuHandler
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function __invoke(QueryInventoryBySku $query, Deferred $promise): void
    {
        $inventory = $this->inventoryProvider->findInventoryById($query->skuId()->toString());

        $promise->resolve($inventory);
    }
}
