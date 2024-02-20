<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderItemPartiallyAdded;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Event\OwnerRequestedOrderCanceled;
use App\Chron\Model\Order\Exception\InsufficientStockForOrderItem;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Exception\OrderAlreadyExists;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

use function in_array;

final class Order implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private OrderOwner $owner;

    private OrderStatus $status;

    private ItemCollection $orderItems;

    private ?string $closedReason = null;

    public static function create(OrderId $orderId, OrderOwner $owner): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $owner, OrderStatus::CREATED));

        return $order;
    }

    public function addOrderItem(OrderItem $orderItem, InventoryReservationService $reservation): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'add order item', $this->status);
        }

        $this->assertOrderItemNotExists($orderItem);

        $quantityReserved = $reservation->reserveItem($orderItem->skuId->toString(), $orderItem->quantity->value);

        if ($quantityReserved === false) {
            throw InsufficientStockForOrderItem::withId($this->orderId(), $orderItem->skuId, $orderItem->orderItemId);
        }

        if ($quantityReserved->value !== $orderItem->quantity->value) {
            $orderItemAdjusted = $orderItem->withAdjustedQuantity(Quantity::create($quantityReserved->value));

            $this->recordThat(OrderItemPartiallyAdded::forOrder(
                $this->orderId(),
                $this->owner,
                $orderItemAdjusted,
                $orderItem->quantity
            ));
        } else {
            $this->recordThat(OrderItemAdded::forOrder($this->orderId(), $this->owner, $orderItem));
        }

        $this->markOrderAsModified(OrderStatus::MODIFIED);
    }

    /**
     * Cancel the order requested by the owner
     *
     * The full order is canceled, and the reserved stock is released
     * Must be called only if the order is pending
     */
    public function cancelByOwner(InventoryReservationService $reservationService): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'cancel by customer', $this->status);
        }

        // release the reserved stock
        $this->orderItems->getItems()->each(function (OrderItem $orderItem) use ($reservationService) {
            // todo add release many items in reservation service
            $reservationService->releaseItem($orderItem->skuId->toString(), $orderItem->quantity->value, InventoryReleaseReason::ORDER_CANCELED);
        });

        $this->recordThat(OwnerRequestedOrderCanceled::forOrder($this->orderId(), $this->owner, OrderStatus::CANCELED, OrderCanceledReason::CUSTOMER_REQUESTED));

        $this->markOrderAsModified(OrderStatus::CANCELED);
    }

    public function orderId(): OrderId
    {
        /** @var AggregateIdentity&OrderId $identity */
        $identity = $this->identity();

        return $identity;
    }

    public function owner(): OrderOwner
    {
        return $this->owner;
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
        $event = OrderModified::forOrder(
            $this->orderId(),
            $this->owner,
            $this->orderItems->calculateBalance(),
            $this->orderItems->calculateQuantity(),
            $orderStatus
        );

        $this->recordThat($event);
    }

    private function isOrderPending(): bool
    {
        return in_array($this->status, [OrderStatus::CREATED, OrderStatus::MODIFIED], true);
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
                $this->owner = $event->orderOwner();
                $this->orderItems = new ItemCollection($this->orderId());
                $this->status = $event->orderStatus();

                break;
            case $event instanceof OrderModified:
                $this->status = $event->orderStatus();

                break;
            case $event instanceof OrderItemAdded:
            case $event instanceof OrderItemPartiallyAdded:
                $this->orderItems->put($event->orderItem());

                break;

            case $event instanceof OwnerRequestedOrderCanceled:
                $this->orderItems = new ItemCollection($this->orderId());
                $this->status = $event->orderStatus();

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
