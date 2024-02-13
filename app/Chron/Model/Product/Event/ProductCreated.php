<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Event;

use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\ProductInfo;
use App\Chron\Model\Product\ProductStatus;
use App\Chron\Model\Product\Sku;
use App\Chron\Model\Product\SkuId;
use Storm\Message\AbstractDomainEvent;

final class ProductCreated extends AbstractDomainEvent
{
    public static function forProduct(Sku $sku, ProductStatus $status): self
    {
        return new self([
            'product_id' => $sku->productId->toString(),
            'sku_id' => $sku->skuId->toString(),
            'sku_code' => $sku->generateSku(),
            'product_info' => $sku->productInfo->toArray(),
            'product_status' => $status->value,
        ]);
    }

    public function aggregateId(): ProductId
    {
        return ProductId::fromString($this->content['product_id']);
    }

    public function skuId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function productInfo(): ProductInfo
    {
        return ProductInfo::fromArray($this->content['product_info']);
    }

    public function productStatus(): ProductStatus
    {
        return ProductStatus::from($this->content['product_status']);
    }

    public function skuCode(): string
    {
        return $this->content['sku_code'];
    }

    public function sku(): Sku
    {
        return new Sku(
            $this->skuId(),
            $this->aggregateId(),
            $this->productInfo()
        );
    }
}
