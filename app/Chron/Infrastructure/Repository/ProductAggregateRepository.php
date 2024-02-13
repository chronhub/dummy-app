<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Product\Product;
use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\Repository\ProductList;
use App\Chron\Package\Aggregate\Contract\AggregateRepository;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use App\Chron\Package\Attribute\AggregateRepository\AsAggregateRepository;

#[AsAggregateRepository(
    chronicler: 'chronicler.event.transactional.standard.pgsql',
    streamName: 'product',
    aggregateRoot: Product::class,
    messageDecorator: 'event.decorator.chain.default'
)]
final readonly class ProductAggregateRepository implements ProductList
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(ProductId $productId): ?Product
    {
        /** @var Product&AggregateRoot $product */
        $product = $this->repository->retrieve($productId);

        return $product;
    }

    public function save(Product $product): void
    {
        $this->repository->store($product);
    }
}
