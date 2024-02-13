<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Product implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private Sku $sku;

    private ProductStatus $status;

    public static function create(ProductId $productId, SkuId $skuId, ProductInfo $productInfo): self
    {
        $self = new self($productId);

        $sku = new Sku($skuId, $productId, $productInfo);

        $self->recordThat(ProductCreated::forProduct($sku, ProductStatus::AVAILABLE));

        return $self;
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    protected function applyProductCreated(ProductCreated $event): void
    {
        $this->status = $event->productStatus();
        $this->sku = $event->sku();
    }
}
