<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Event\OrderCanceled;
use App\Chron\Model\Order\Event\OrderClosed;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderDelivered;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Order\Event\OrderRefunded;
use App\Chron\Model\Order\Event\OrderReturned;
use App\Chron\Model\Order\Event\OrderShipped;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Service\CanReturnOrder;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;

final class OrderBck implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerId $customerId;

    private OrderStatus $status;

    private Balance $balance;

    private Quantity $quantity;

    private ItemCollection $orderItems;

    private ?string $closedReason = null;

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $customerId, OrderStatus::CREATED));

        return $order;
    }

    //    public function modify(Amount $amount): void
    //    {
    //        if (! $this->isOrderPending()) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'modify', $this->status);
    //        }
    //
    //        $this->recordThat(OrderModified::forCustomer($this->orderId(), $this->customerId, OrderStatus::MODIFIED, clone $amount));
    //    }
    //
    //    public function pay(): void
    //    {
    //        if ($this->status !== OrderStatus::MODIFIED) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'pay', $this->status);
    //        }
    //
    //        if ($this->balance->toFloat() <= 0) {
    //            throw InvalidOrderOperation::withInvalidBalance($this->orderId(), 'pay', $this->balance());
    //        }
    //
    //        $this->recordThat(OrderPaid::forCustomer($this->orderId(), $this->customerId, OrderStatus::PAID));
    //    }
    //
    //    public function ship(): void
    //    {
    //        if ($this->status !== OrderStatus::PAID) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'ship', $this->status);
    //        }
    //
    //        $this->recordThat(OrderShipped::forCustomer($this->orderId(), $this->customerId, OrderStatus::SHIPPED));
    //    }
    //
    //    public function deliver(): void
    //    {
    //        if ($this->status !== OrderStatus::SHIPPED) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'deliver', $this->status);
    //        }
    //
    //        $this->recordThat(OrderDelivered::forCustomer($this->orderId(), $this->customerId, OrderStatus::DELIVERED));
    //    }
    //
    //    public function return(CanReturnOrder $allowReturn): void
    //    {
    //        if ($this->status !== OrderStatus::DELIVERED) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'return', $this->status);
    //        }
    //
    //        //fixMe never refund
    //        if (! $allowReturn($this->orderId(), $this->customerId)) {
    //            throw InvalidOrderOperation::returnOrderDisallowByPolicy($this->orderId());
    //        }
    //
    //        $this->recordThat(OrderReturned::forCustomer($this->orderId(), $this->customerId, OrderStatus::RETURNED));
    //    }
    //
    //    public function refund(): void
    //    {
    //        if ($this->status !== OrderStatus::RETURNED) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'refund', $this->status);
    //        }
    //
    //        $this->recordThat(OrderRefunded::forCustomer($this->orderId(), $this->customerId, OrderStatus::REFUNDED, $this->balance()));
    //    }
    //
    //    public function cancel(): void
    //    {
    //        if (! $this->isOrderPending()) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'cancel', $this->status);
    //        }
    //
    //        $this->recordThat(OrderCanceled::forCustomer($this->orderId(), $this->customerId, OrderStatus::CANCELLED));
    //    }
    //
    //    public function close(CanReturnOrder $allowReturn): void
    //    {
    //        if (! $this->canCloseOrder()) {
    //            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'close', $this->status);
    //        }
    //
    //        if ($allowReturn($this->orderId(), $this->customerId)) {
    //            throw InvalidOrderOperation::closeOrderDisallowByPolicy($this->orderId());
    //        }
    //
    //        $reason = match ($this->status) {
    //            OrderStatus::DELIVERED => 'Order returned is overdue',
    //            OrderStatus::REFUNDED => 'Order refunded',
    //            OrderStatus::CANCELLED => 'Order cancelled',
    //            default => throw new RuntimeException('Close order operation does not support status: '.$this->status->value),
    //        };
    //
    //        $this->recordThat(OrderClosed::forCustomer($this->orderId(), $this->customerId, OrderStatus::CLOSED, $reason));
    //    }

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
        $this->customerId = $event->orderOwner();
        $this->status = $event->orderStatus();
        $this->balance = Balance::newInstance();
        $this->orderItems = new ItemCollection();
    }

    protected function applyOrderModified(OrderModified $event): void
    {
        $this->balance = $event->balance();
        $this->quantity = $event->quantity();
        $this->status = $event->orderStatus();
    }

    protected function applyOrderItemAdded(OrderItemAdded $event): void
    {
        $this->orderItems->put($event->orderItem());
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
            || $this->status === OrderStatus::CANCELED;
    }
}
