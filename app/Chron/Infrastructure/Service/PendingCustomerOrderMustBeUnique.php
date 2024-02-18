<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Service\UniquePendingCustomerOrder;
use App\Chron\Projection\Provider\OrderProvider;
use stdClass;

final readonly class PendingCustomerOrderMustBeUnique implements UniquePendingCustomerOrder
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function hasPendingOrder(CustomerId $customerId): bool
    {
        $order = $this->orderProvider->findPendingOrderOfCustomer($customerId->toString());

        return $order instanceof stdClass;
    }
}
