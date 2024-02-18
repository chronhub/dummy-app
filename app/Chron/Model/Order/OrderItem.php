<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\UnitPrice;

final readonly class OrderItem
{
    private function __construct(
        public OrderItemId $orderItemId,
        public SkuId $skuId,
        public UnitPrice $unitPrice,
        public Quantity $quantity,
    ) {
    }

    public static function fromArray(array $item): self
    {
        return new self(
            OrderItemId::fromString($item['order_item_id']),
            SkuId::fromString($item['sku_id']),
            UnitPrice::create($item['unit_price']),
            Quantity::create($item['quantity']),
        );
    }

    public static function fromValues(OrderItemId $orderItemId, SkuId $skuId, UnitPrice $unitPrice, Quantity $quantity): self
    {
        return new self($orderItemId, $skuId, $unitPrice, $quantity);
    }

    public function withAdjustedQuantity(Quantity $quantity): self
    {
        return new self(
            $this->orderItemId,
            $this->skuId,
            $this->unitPrice,
            Quantity::create($quantity->value),
        );
    }

    /**
     * @return array{order_item_id: string, sku_id: string, unit_price: string, quantity: int}
     */
    public function toArray(): array
    {
        return [
            'order_item_id' => $this->orderItemId->toString(),
            'sku_id' => $this->skuId->toString(),
            'unit_price' => $this->unitPrice->value,
            'quantity' => $this->quantity->value,
        ];
    }
}
