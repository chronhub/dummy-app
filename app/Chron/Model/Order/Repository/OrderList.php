<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Repository;

use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;

interface OrderList
{
    public function get(OrderId $orderId): ?Order;

    public function save(Order $order): void;
}
