<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderItemId;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\Quantity;
use Storm\Message\AbstractDomainCommand;

final class AddOrderItem extends AbstractDomainCommand
{
    public static function forOrder(string $orderId, string $orderItemId, string $skuId, string $customerId, string $unitPrice, int $quantity): self
    {
        return new self([
            'order_id' => $orderId,
            'order_owner' => $customerId,
            'order_item_id' => $orderItemId,
            'sku_id' => $skuId,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
        ]);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function orderItem(): OrderItem
    {
        return OrderItem::fromValues(
            $this->orderItemId(),
            $this->skuId(),
            $this->unitPrice(),
            $this->quantity()
        );
    }

    public function orderItemId(): OrderItemId
    {
        return OrderItemId::fromString($this->content['order_item_id']);
    }

    public function skuId(): SkuId
    {
        return SkuId::fromString($this->content['sku_id']);
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->content['order_owner']);
    }

    public function unitPrice(): UnitPrice
    {
        return UnitPrice::create($this->content['unit_price']);
    }

    public function quantity(): Quantity
    {
        return Quantity::create($this->content['quantity']);
    }
}
