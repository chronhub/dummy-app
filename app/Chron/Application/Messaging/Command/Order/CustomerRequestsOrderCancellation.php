<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use Storm\Message\AbstractDomainCommand;

final class CustomerRequestsOrderCancellation extends AbstractDomainCommand
{
    public static function forOrder(string $orderId, string $customerId): self
    {
        return new self([
            'order_id' => $orderId,
            'customer_id' => $customerId,
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
