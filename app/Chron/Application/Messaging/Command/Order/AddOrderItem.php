<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use Storm\Message\AbstractDomainCommand;

final class AddOrderItem extends AbstractDomainCommand
{
    public static function forOrder(string $orderId, string $orderItemId, string $skuId, string $productId, string $customerId, string $unitPrice, int $quantity): self
    {
        return new self([
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'order_item_id' => $orderItemId,
            'sku_id' => $skuId,
            'product_id' => $productId,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
        ]);
    }
}
