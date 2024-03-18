<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Order\Service\UniqueOwnerPendingOrder;
use App\Chron\Projection\Provider\OrderProvider;
use stdClass;

final readonly class OwnerPendingOrderMustBeUnique implements UniqueOwnerPendingOrder
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function hasPendingOrder(string $ownerOrder): bool
    {
        $order = $this->orderProvider->findPendingOwnerOrder($ownerOrder);

        return $order instanceof stdClass;
    }
}
