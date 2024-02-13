<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Event\OrderCanceled;
use App\Chron\Model\Order\Event\OrderClosed;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderDelivered;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderItemPartiallyAdded;
use App\Chron\Model\Order\Event\OrderItemQuantityDecreased;
use App\Chron\Model\Order\Event\OrderItemQuantityIncreased;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Order\Event\OrderRefunded;
use App\Chron\Model\Order\Event\OrderReturned;
use App\Chron\Model\Order\Event\OrderShipped;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Service\CanReturnOrder;
use App\Chron\Model\Order\Service\OrderReservationService;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use Illuminate\Support\Collection;
use RuntimeException;

final class Order implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerId $customerId;

    private OrderStatus $status;

    private Balance $balance;

    private Collection $orderItems;

    private ?string $closedReason = null;

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $customerId, OrderStatus::CREATED));

        return $order;
    }

    public function addOrderItem(OrderItem $orderItem, OrderReservationService $reservationService): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'modify', $this->status);
        }

        if (! $this->orderItems->has($orderItem->orderItemId->toString())) {
            $reserved = $reservationService->reserveItem($orderItem->skuId->toString(), $orderItem->productId->toString(), $orderItem->quantity->value);

            if ($reserved > $orderItem->quantity->value) {
                throw new RuntimeException('Reservation service returned more than expected quantity');
            } elseif ($reserved === $orderItem->quantity->value) {
                $this->recordThat(OrderItemAdded::forOrder($this->orderId(), $this->customerId, $orderItem));
            } elseif ($reserved === 0) {
                throw new RuntimeException('Out of stock for order item');
            } else {
                $partialQuantity = Quantity::create($reserved);
                $partialItem = $orderItem->withAdjustedQuantity($partialQuantity);

                $this->recordThat(OrderItemPartiallyAdded::forOrder($this->orderId(), $this->customerId, $partialItem, $orderItem->quantity));
            }
        } else {
            $saveItem = $this->orderItems->get($orderItem->orderItemId->toString());

            if ($saveItem->quantity->value === $orderItem->quantity->value) {
                return;
            }

            // decrease quantity
            if ($saveItem->quantity->value > $orderItem->quantity->value) {
                $quantityToRelease = $saveItem->quantity->value - $orderItem->quantity->value;

                $released = $reservationService->releaseItem($orderItem->skuId->toString(), $orderItem->productId->toString(), $quantityToRelease);
                if ($released) {
                    $this->recordThat(OrderItemQuantityDecreased::forOrder($this->orderId(), $this->customerId, $orderItem, $saveItem->quantity));
                } else {
                    throw new RuntimeException('Reservation service failed to release quantity');
                }
            } else {
                // increase quantity
                $reserved = $reservationService->reserveItem($orderItem->skuId->toString(), $orderItem->productId->toString(), $orderItem->quantity->value);

                if ($reserved > $orderItem->quantity->value) {
                    throw new RuntimeException('Reservation service returned more than expected quantity');
                } elseif ($reserved === $orderItem->quantity->value) {
                    $this->recordThat(OrderItemQuantityIncreased::forOrder($this->orderId(), $this->customerId, $orderItem, $saveItem->quantity));
                } elseif ($reserved === 0) {
                    throw new RuntimeException('Out of stock for order item');
                } else {
                    $partialQuantity = Quantity::create($reserved);
                    $partialItem = $orderItem->withAdjustedQuantity($partialQuantity);

                    $this->recordThat(OrderItemPartiallyAdded::forOrder($this->orderId(), $this->customerId, $partialItem, $orderItem->quantity));
                }
            }
        }
    }

    public function modify(Amount $amount): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'modify', $this->status);
        }

        $this->recordThat(OrderModified::forCustomer($this->orderId(), $this->customerId, OrderStatus::MODIFIED, clone $amount));
    }

    public function pay(): void
    {
        if ($this->status !== OrderStatus::MODIFIED) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'pay', $this->status);
        }

        if ($this->balance->toFloat() <= 0) {
            throw InvalidOrderOperation::withInvalidBalance($this->orderId(), 'pay', $this->balance());
        }

        $this->recordThat(OrderPaid::forCustomer($this->orderId(), $this->customerId, OrderStatus::PAID));
    }

    public function ship(): void
    {
        if ($this->status !== OrderStatus::PAID) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'ship', $this->status);
        }

        $this->recordThat(OrderShipped::forCustomer($this->orderId(), $this->customerId, OrderStatus::SHIPPED));
    }

    public function deliver(): void
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'deliver', $this->status);
        }

        $this->recordThat(OrderDelivered::forCustomer($this->orderId(), $this->customerId, OrderStatus::DELIVERED));
    }

    public function return(CanReturnOrder $allowReturn): void
    {
        if ($this->status !== OrderStatus::DELIVERED) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'return', $this->status);
        }

        //fixMe never refund
        if (! $allowReturn($this->orderId(), $this->customerId)) {
            throw InvalidOrderOperation::returnOrderDisallowByPolicy($this->orderId());
        }

        $this->recordThat(OrderReturned::forCustomer($this->orderId(), $this->customerId, OrderStatus::RETURNED));
    }

    public function refund(): void
    {
        if ($this->status !== OrderStatus::RETURNED) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'refund', $this->status);
        }

        $this->recordThat(OrderRefunded::forCustomer($this->orderId(), $this->customerId, OrderStatus::REFUNDED, $this->balance()));
    }

    public function cancel(): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'cancel', $this->status);
        }

        $this->recordThat(OrderCanceled::forCustomer($this->orderId(), $this->customerId, OrderStatus::CANCELLED));
    }

    public function close(CanReturnOrder $allowReturn): void
    {
        if (! $this->canCloseOrder()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'close', $this->status);
        }

        if ($allowReturn($this->orderId(), $this->customerId)) {
            throw InvalidOrderOperation::closeOrderDisallowByPolicy($this->orderId());
        }

        $reason = match ($this->status) {
            OrderStatus::DELIVERED => 'Order returned is overdue',
            OrderStatus::REFUNDED => 'Order refunded',
            OrderStatus::CANCELLED => 'Order cancelled',
            default => throw new RuntimeException('Close order operation does not support status: '.$this->status->value),
        };

        $this->recordThat(OrderClosed::forCustomer($this->orderId(), $this->customerId, OrderStatus::CLOSED, $reason));
    }

    public function orderId(): OrderId
    {
        // fixMe: This is a workaround for non detected type hinting
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
        return clone $this->balance;
    }

    public function closedReason(): ?string
    {
        return $this->closedReason;
    }

    protected function applyOrderCreated(OrderCreated $event): void
    {
        $this->customerId = $event->customerId();
        $this->status = $event->orderStatus();
        $this->balance = Balance::newInstance();
        $this->orderItems = new Collection();
    }

    protected function applyOrderModified(OrderModified $event): void
    {
        $this->status = $event->orderStatus();
        $this->balance->add($event->amount());
    }

    protected function applyOrderPaid(OrderPaid $event): void
    {
        $this->status = $event->orderStatus();
    }

    protected function applyOrderDelivered(OrderDelivered $event): void
    {
        $this->status = $event->orderStatus();
    }

    protected function applyOrderReturned(OrderReturned $event): void
    {
        $this->status = $event->orderStatus();
    }

    protected function applyOrderRefunded(OrderRefunded $event): void
    {
        $this->status = $event->orderStatus();
        $this->balance = Balance::newInstance();
    }

    protected function applyOrderShipped(OrderShipped $event): void
    {
        $this->status = $event->orderStatus();
    }

    protected function applyOrderCanceled(OrderCanceled $event): void
    {
        $this->status = $event->orderStatus();
    }

    protected function applyOrderClosed(OrderClosed $event): void
    {
        $this->status = $event->orderStatus();
        $this->closedReason = $event->reason();
    }

    private function isOrderPending(): bool
    {
        return $this->status === OrderStatus::CREATED || $this->status === OrderStatus::MODIFIED;
    }

    private function canCloseOrder(): bool
    {
        return $this->status === OrderStatus::DELIVERED
            || $this->status === OrderStatus::REFUNDED
            || $this->status === OrderStatus::CANCELLED;
    }
}
