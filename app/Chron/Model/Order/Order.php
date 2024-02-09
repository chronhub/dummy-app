<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Event\OrderCanceled;
use App\Chron\Model\Order\Event\OrderCompleted;
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

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $order = new self($orderId);
        $order->recordThat(OrderCreated::forCustomer($orderId, $customerId, OrderStatus::CREATED));

        return $order;
    }

    public function modify(): void
    {
        if ($this->status->isPending()) {
            $this->recordThat(OrderModified::forCustomer($this->orderId(), $this->customerId, OrderStatus::MODIFIED));
        }

        throw InvalidOrderOperation::withStatus($this->orderId(), 'modify', $this->status);
    }

    // bring a stub payment service
    // add a stub amount
    public function pay(): void
    {
        if (! $this->status->isPending()) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'pay', $this->status);
        }

        $this->recordThat(OrderPaid::forCustomer($this->orderId(), $this->customerId, OrderStatus::PAID));
    }

    public function cancel(): void
    {
        if (! $this->status->isPending()) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'cancel', $this->status);
        }

        $this->recordThat(OrderCanceled::forCustomer($this->orderId(), $this->customerId, OrderStatus::CANCELLED));
    }

    public function deliver(): void
    {
        if ($this->status !== OrderStatus::PAID) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'deliver', $this->status);
        }

        $this->recordThat(OrderDelivered::forCustomer($this->orderId(), $this->customerId, OrderStatus::DELIVERED));
    }

    public function ship(): void
    {
        if ($this->status !== OrderStatus::DELIVERED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'ship', $this->status);
        }

        $this->recordThat(OrderShipped::forCustomer($this->orderId(), $this->customerId, OrderStatus::SHIPPED));
    }

    public function return(): void
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'return', $this->status);
        }

        $this->recordThat(OrderReturned::forCustomer($this->orderId(), $this->customerId, OrderStatus::RETURNED));
    }

    public function refund(): void
    {
        if ($this->status !== OrderStatus::RETURNED) {
            throw InvalidOrderOperation::withStatus($this->orderId(), 'refund', $this->status);
        }

        $this->recordThat(OrderRefunded::forCustomer($this->orderId(), $this->customerId, OrderStatus::REFUNDED));
    }

    /**
     * @deprecated
     */
    public function complete(): void
    {
        if ($this->status === OrderStatus::COMPLETED) {
            return;
        }

        $this->recordThat(OrderCompleted::forCustomer($this->orderId(), $this->customerId, OrderStatus::COMPLETED));
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

    protected function applyOrderCreated(OrderCreated $event): void
    {
        $this->customerId = $event->customerId();
        $this->status = $event->orderStatus();
    }

    protected function applyOrderCompleted(OrderCompleted $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderModified(OrderModified $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderPaid(OrderPaid $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderDelivered(OrderDelivered $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderReturned(OrderReturned $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderRefunded(OrderRefunded $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderCancelled(OrderCanceled $event): void
    {
        $this->status = $event->status();
    }
}
