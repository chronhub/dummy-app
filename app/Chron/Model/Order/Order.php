<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Event\CustomerRequestedOrderCanceled;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderItemPartiallyAdded;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Exception\OrderAlreadyExists;
use App\Chron\Model\Order\Exception\ReservationOrderItemFailed;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

final class Order implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerId $customerId;

    private OrderStatus $status;

    private ItemCollection $orderItems;

    private ?string $closedReason = null;

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $customerId, OrderStatus::CREATED));

        return $order;
    }

    public function addOrderItem(OrderItem $orderItem, InventoryReservationService $reservation): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'modify', $this->status);
        }

        $this->assertOrderItemNotExists($orderItem);

        $quantityReserved = $reservation->reserveItem($orderItem->skuId->toString(), $orderItem->quantity->value);

        if ($quantityReserved === false) {
            throw ReservationOrderItemFailed::withReason(
                $orderItem->skuId,
                $this->orderId(),
                $orderItem->orderItemId,
                'Insufficient stock'
            );
        }

        if ($quantityReserved->value !== $orderItem->quantity->value) {
            $orderItem->withAdjustedQuantity(Quantity::create($quantityReserved->value));

            $this->recordThat(OrderItemPartiallyAdded::forOrder(
                $this->orderId(),
                $this->customerId,
                $orderItem,
                Quantity::create($quantityReserved->value)
            ));
        } else {
            $this->recordThat(OrderItemAdded::forOrder($this->orderId(), $this->customerId, $orderItem));
        }

        $this->markOrderAsModified(OrderStatus::MODIFIED);
    }

    /**
     * Cancel the order by the customer request
     *
     * The full order is canceled, and the reserved stock is released
     * Must be called only if the order is pending
     */
    public function cancelByCustomer(InventoryReservationService $reservationService): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'cancel by customer', $this->status);
        }

        $orderReason = OrderCanceledReason::CUSTOMER_REQUESTED;
        $inventoryReason = InventoryReleaseReason::ORDER_CANCELED; // need to conform to the inventory reason

        // release the reserved stock
        $this->orderItems->getItems()->each(function (OrderItem $orderItem) use ($reservationService, $inventoryReason) {
            $reservationService->releaseItem($orderItem->skuId->toString(), $orderItem->quantity->value, $inventoryReason);
        });

        $this->recordThat(CustomerRequestedOrderCanceled::forOrder($this->orderId(), $this->customerId, OrderStatus::CANCELED, $orderReason));

        $this->markOrderAsModified(OrderStatus::CANCELED);
    }

    public function orderId(): OrderId
    {
        /** @var AggregateIdentity&OrderId $identity */
        $identity = $this->identity();

        return $identity;
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function balance(): Balance
    {
        return $this->orderItems->calculateBalance();
    }

    public function quantity(): Quantity
    {
        return $this->orderItems->calculateQuantity();
    }

    public function closedReason(): ?string
    {
        return $this->closedReason;
    }

    private function markOrderAsModified(OrderStatus $orderStatus): void
    {
        $event = OrderModified::forCustomer(
            $this->orderId(),
            $this->customerId,
            $this->orderItems->calculateBalance(),
            $this->orderItems->calculateQuantity(),
            $orderStatus
        );

        $this->recordThat($event);
    }

    private function isOrderPending(): bool
    {
        return $this->status === OrderStatus::CREATED || $this->status === OrderStatus::MODIFIED;
    }

    private function assertOrderItemNotExists(OrderItem $orderItem): void
    {
        if ($this->orderItems->has($orderItem)) {
            throw OrderAlreadyExists::withOrderItemId($this->orderId(), $orderItem->orderItemId);
        }
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof OrderCreated:
                $this->customerId = $event->customerId();
                $this->orderItems = new ItemCollection($this->orderId());
                $this->status = $event->orderStatus();

                break;
            case $event instanceof OrderModified:
                $this->status = $event->orderStatus();

                break;
            case $event instanceof OrderItemAdded:
                $this->orderItems->put($event->orderItem());

                break;

            case $event instanceof OrderItemPartiallyAdded:
                $this->orderItems->put($event->orderItem());

                break;

            case $event instanceof CustomerRequestedOrderCanceled:
                $this->orderItems = new ItemCollection($this->orderId());
                $this->status = $event->orderStatus();

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
