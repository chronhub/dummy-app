<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Aggregate\AggregateBehaviorTrait;
use App\Chron\Aggregate\Contract\AggregateIdentity;
use App\Chron\Aggregate\Contract\AggregateRoot;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Event\OrderCompleted;
use App\Chron\Model\Order\Event\OrderCreated;

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
}
