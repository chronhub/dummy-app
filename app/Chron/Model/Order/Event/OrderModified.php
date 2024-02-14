<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Balance;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use App\Chron\Model\Order\Quantity;
use Storm\Message\AbstractDomainEvent;

final class OrderModified extends AbstractDomainEvent
{
    public static function forCustomer(OrderId $orderId, CustomerId $customerId, Balance $balance, Quantity $quantity, OrderStatus $orderStatus): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'customer_id' => $customerId->toString(),
            'balance' => $balance->value(),
            'quantity' => $quantity->value,
            'order_status' => $orderStatus->value,
        ]);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function orderStatus(): OrderStatus
    {
        return OrderStatus::from($this->content['order_status']);
    }

    public function balance(): Balance
    {
        return Balance::fromString($this->content['balance']);
    }

    public function quantity(): Quantity
    {
        return Quantity::create($this->content['quantity']);
    }
}
