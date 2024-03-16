<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

use function in_array;

final class Order implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private OrderOwner $owner;

    private ItemCollection $orderItems;

    private OrderStatus $status;

    private ?string $closedReason = null;

    public static function create(OrderId $orderId, OrderOwner $owner, ItemCollection $items): self
    {
        $order = new self($orderId);

        $order->recordThat(OrderCreated::forCustomer($orderId, $owner, $items, OrderStatus::CREATED));

        return $order;
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

    private function isOrderPending(): bool
    {
        return in_array($this->status, OrderStatus::pending(), true);
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof OrderCreated:
                $this->owner = $event->orderOwner();
                $this->orderItems = $event->orderItems();
                $this->status = $event->orderStatus();

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
