<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Infrastructure\Service\PaymentGateway;
use App\Chron\Model\InvalidDomainException;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Exception\OrderException;
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

    public function pay(PaymentGateway $paymentGateway): void
    {
        if ($this->status !== OrderStatus::CREATED) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'pay', $this->status());
        }

        $success = $paymentGateway->process($this->orderId(), $this->owner, $this->balance());

        if (! $success) {
            throw new OrderException('Payment failed');
        }

        $this->recordThat(OrderPaid::forOrder($this->orderId(), $this->owner, $this->orderItems, OrderStatus::PAID));
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

    public function items(): ItemCollection
    {
        return $this->orderItems;
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

            case $event instanceof OrderPaid:
                $this->status = $event->orderStatus();

                break;
            default:
                throw InvalidDomainException::eventNotSupported(self::class, $event::class);
        }
    }
}
