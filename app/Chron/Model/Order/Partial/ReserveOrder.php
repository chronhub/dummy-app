<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Partial;

use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderItemPartiallyAdded;
use App\Chron\Model\Order\Exception\InsufficientStockForOrderItem;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\Quantity;

final readonly class ReserveOrder
{
    public function __construct(private InventoryReservationService $reservation)
    {
    }

    /**
     * @throws InsufficientStockForOrderItem
     */
    public function reserve(OrderId $orderId, OrderOwner $orderOwner, OrderItem $orderItem): OrderItemAdded|OrderItemPartiallyAdded
    {
        $quantityReserved = $this->doReservation($orderId, $orderItem);

        if ($quantityReserved->value !== $orderItem->quantity->value) {
            $orderItemAdjusted = $orderItem->withAdjustedQuantity(Quantity::create($quantityReserved->value));

            return OrderItemPartiallyAdded::forOrder(
                $orderId,
                $orderOwner,
                $orderItemAdjusted,
                $orderItem->quantity
            );
        }

        return OrderItemAdded::forOrder($orderId, $orderOwner, $orderItem);
    }

    private function doReservation(OrderId $orderId, OrderItem $orderItem): PositiveQuantity
    {
        $quantityReserved = $this->reservation->reserveItem($orderItem->skuId->toString(), $orderItem->quantity->value);

        if ($quantityReserved === false) {
            throw InsufficientStockForOrderItem::withId($orderId, $orderItem->skuId, $orderItem->orderItemId);
        }

        return $quantityReserved;
    }
}
