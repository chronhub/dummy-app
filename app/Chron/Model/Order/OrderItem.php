<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\SkuId;

final readonly class OrderItem
{
    private function __construct(
        public OrderItemId $orderItemId,
        public SkuId $skuId,
        public ProductId $productId,
        public Quantity $quantity,
    ) {
    }

    public static function fromArray(array $item): self
    {
        return new self(
            OrderItemId::fromString($item['order_item_id']),
            SkuId::fromString($item['sku_id']),
            ProductId::fromString($item['product_id']),
            Quantity::create($item['quantity']),
        );
    }

    public function withAdjustedQuantity(Quantity $quantity): self
    {
        return new self(
            $this->orderItemId,
            $this->skuId,
            $this->productId,
            Quantity::create($quantity->value),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->orderItemId->toString(),
            'sku_id' => $this->skuId->toString(),
            'product_id' => $this->productId->toString(),
            'quantity' => $this->quantity->value,
        ];
    }
}
