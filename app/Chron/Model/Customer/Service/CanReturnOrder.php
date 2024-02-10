<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Infrastructure\Service\CustomerOrderProvider;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use DateInterval;
use Storm\Contract\Clock\SystemClock;

final readonly class CanReturnOrder
{
    public function __construct(
        private CustomerOrderProvider $customerOrderProvider,
        private SystemClock $clock
    ) {
    }

    public function __invoke(OrderId $orderId, CustomerId $customerId): bool
    {
        $order = $this->customerOrderProvider->findOrderOfCustomer($customerId->toString(), $orderId->toString());

        if (OrderStatus::tryFrom($order->order_status) !== OrderStatus::DELIVERED) {
            return false;
        }

        $deliveredTime = $this->clock->toDateTimeImmutable($order->created_at);

        return $deliveredTime > $this->clock->now()->sub(new DateInterval('PT5M'));
    }
}
