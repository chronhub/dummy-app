<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use Storm\Message\AbstractDomainEvent;

final class OrderPaid extends AbstractDomainEvent
{
    public static function forCustomer(OrderId $orderId, CustomerId $customerId, OrderStatus $orderStatus): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'customer_id' => $customerId->toString(),
            'order_status' => $orderStatus->value,
        ]);
    }

    public function orderId(): string
    {
        return $this->content['order_id'];
    }

    public function customerId(): string
    {
        return $this->content['customer_id'];
    }

    public function status(): OrderStatus
    {
        return OrderStatus::from($this->content['order_status']);
    }
}
