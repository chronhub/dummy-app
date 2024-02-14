<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\Stock;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;
use Storm\Contract\Message\DomainEvent;

use function sprintf;

final class Order implements AggregateRoot
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

    public function addOrderItem(OrderItem $orderItem, Inventory $inventory): void
    {
        if (! $this->isOrderPending()) {
            throw InvalidOrderOperation::withInvalidStatus($this->orderId(), 'modify', $this->status);
        }

        if (! $this->orderItems->has($orderItem)) {
            // assume fully available stock
            if ($inventory->canReserve(Stock::create($orderItem->quantity->value))) {

                $inventory->reserve(Stock::create($orderItem->quantity->value));

                $this->recordThat(OrderItemAdded::forOrder(
                    $this->orderId(),
                    $this->customerId,
                    $orderItem,
                ));

                $this->recordThat(OrderModified::forCustomer(
                    $this->orderId(),
                    $this->customerId,
                    $this->orderItems->calculateBalance(),
                    $this->orderItems->calculateQuantity(),
                    OrderStatus::MODIFIED)
                );
            } else {
                throw new RuntimeException('Not enough stock');
            }
        } else {
            logger('Order item already exists');
        }
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

    private function isOrderPending(): bool
    {
        return $this->status === OrderStatus::CREATED || $this->status === OrderStatus::MODIFIED;
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof OrderCreated:
                $this->customerId = $event->customerId();
                $this->status = $event->orderStatus();
                $this->balance = Balance::newInstance();
                $this->orderItems = new ItemCollection();

                break;
            case $event instanceof OrderModified:
                $this->balance = $event->balance();
                $this->quantity = $event->quantity();
                $this->status = $event->orderStatus();

                break;
            case $event instanceof OrderItemAdded:
                $this->orderItems->put($event->orderItem());

                break;
            default:
                throw new RuntimeException(sprintf('Unknown order event %s', $event::class));
        }
    }
}
