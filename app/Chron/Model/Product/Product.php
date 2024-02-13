<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Product implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private ProductInfo $info;

    private ProductStatus $status;

    public static function create(ProductId $productId, ProductInfo $productInfo): self
    {
        $self = new self($productId);

        $self->recordThat(ProductCreated::forProduct($productId, $productInfo, ProductStatus::AVAILABLE));

        return $self;
    }

    public function info(): ProductInfo
    {
        return $this->info;
    }

    protected function applyProductCreated(ProductCreated $event): void
    {
        $this->info = $event->productInfo();
        $this->status = $event->productStatus();
    }
}
