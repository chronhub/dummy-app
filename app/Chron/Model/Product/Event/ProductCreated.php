<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Event;

use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\ProductInfo;
use App\Chron\Model\Product\ProductStatus;
use Storm\Message\AbstractDomainEvent;

final class ProductCreated extends AbstractDomainEvent
{
    public static function forProduct(ProductId $productId, ProductInfo $productInfo, ProductStatus $status): self
    {
        return new self([
            'product_id' => $productId->toString(),
            'product_info' => $productInfo->toArray(),
            'product_status' => $status->value,
        ]);
    }

    public function aggregateId(): ProductId
    {
        return ProductId::fromString($this->content['product_id']);
    }

    public function productInfo(): ProductInfo
    {
        return ProductInfo::fromArray($this->content['product_info']);
    }

    public function productStatus(): ProductStatus
    {
        return ProductStatus::from($this->content['product_status']);
    }
}
