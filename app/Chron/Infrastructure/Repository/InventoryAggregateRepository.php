<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\SkuId;
use Storm\Aggregate\Attribute\AsAggregateRepository;
use Storm\Contract\Aggregate\AggregateRepository;
use Storm\Contract\Aggregate\AggregateRoot;

#[AsAggregateRepository(
    chronicler: 'chronicler.event.transactional.standard.pgsql',
    streamName: 'inventory',
    aggregateRoot: Inventory::class,
    messageDecorator: 'event.decorator.chain.default'
)]
final readonly class InventoryAggregateRepository implements InventoryList
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(SkuId $skuId): ?Inventory
    {
        /** @var Inventory&AggregateRoot $inventory */
        $inventory = $this->repository->retrieve($skuId);

        return $inventory;
    }

    public function save(Inventory $inventory): void
    {
        $this->repository->store($inventory);
    }
}
