<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Command\Order\CompleteOrder;
use App\Chron\Package\Reporter\Report;
use Illuminate\Database\Connection;

use function json_decode;

class OrderService
{
    public function completeOrder(): void
    {
        $order = $this->findPendingOrder();

        if ($order === null) {
            return;
        }

        $command = CompleteOrder::forCustomer($order['order_id'], $order['customer_id']);

        Report::relay($command);
    }

    protected function findPendingOrder(): ?array
    {
        /** @var Connection $connection */
        $connection = app('db.connection');

        $count = 0;
        while (true) {
            $order = $connection->table('order')->inRandomOrder()->first();

            $content = json_decode($order->content, true);

            $status = $content['order_status'] ?? null;

            if ($status === 'created') {
                return $content;
            }

            if ($count > 10) {
                logger('No pending order found');

                break;
            }

            $count++;
        }

        return null;
    }
}
