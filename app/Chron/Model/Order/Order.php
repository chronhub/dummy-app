<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Event\OrderCanceled;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderDelivered;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Order\Event\OrderRefunded;
use App\Chron\Model\Order\Event\OrderReturned;
use App\Chron\Model\Order\Event\OrderShipped;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final class Order implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerId $customerId;

    private OrderStatus $status;

    private Balance $balance;

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $customerId, OrderStatus::CREATED));

        return $order;
    }

    public function modify(Amount $amount): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'modify', $this->status);
        }

        $this->recordThat(OrderModified::forCustomer($this->orderId(), $this->customerId, OrderStatus::MODIFIED, clone $amount));
    }

    public function pay(): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'pay', $this->status);
        }

        if ($this->balance->toFloat() < 0) {
            throw InvalidOrderOperation::withBalance($this->orderId(), 'pay', $this->balance());
        }

        $this->recordThat(OrderPaid::forCustomer($this->orderId(), $this->customerId, OrderStatus::PAID));
    }

    public function ship(): void
    {
        if ($this->status !== OrderStatus::PAID) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'ship', $this->status);
        }

        $this->recordThat(OrderShipped::forCustomer($this->orderId(), $this->customerId, OrderStatus::SHIPPED));
    }

    public function deliver(): void
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'deliver', $this->status);
        }

        $this->recordThat(OrderDelivered::forCustomer($this->orderId(), $this->customerId, OrderStatus::DELIVERED));
    }

    public function return(): void
    {
        if ($this->status !== OrderStatus::DELIVERED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'return', $this->status);
        }

        $this->recordThat(OrderReturned::forCustomer($this->orderId(), $this->customerId, OrderStatus::RETURNED));
    }

    public function refund(): void
    {
        if ($this->status !== OrderStatus::RETURNED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'refund', $this->status);
        }

        $this->recordThat(OrderRefunded::forCustomer($this->orderId(), $this->customerId, OrderStatus::REFUNDED, $this->balance()));
    }

    public function cancel(): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'cancel', $this->status);
        }

        $this->recordThat(OrderCanceled::forCustomer($this->orderId(), $this->customerId, OrderStatus::CANCELLED));
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

    protected function applyOrderCreated(OrderCreated $event): void
    {
        $this->customerId = $event->customerId();
        $this->status = $event->orderStatus();
        $this->balance = Balance::newInstance();
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

    private function isOrderPending(): bool
    {
        return $this->status === OrderStatus::CREATED || $this->status === OrderStatus::MODIFIED;
    }
}
