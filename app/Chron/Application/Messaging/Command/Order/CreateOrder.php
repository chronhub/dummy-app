<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use Storm\Message\AbstractDomainCommand;

final class CreateOrder extends AbstractDomainCommand
{
    public static function forCustomer(string $customerId, string $orderId): self
    {
        return new self([
            'customer_id' => $customerId,
            'order_id' => $orderId,
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
}
