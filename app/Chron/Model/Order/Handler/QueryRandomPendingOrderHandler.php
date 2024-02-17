<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Query\QueryRandomPendingOrder;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use Illuminate\Database\Connection;
use React\Promise\Deferred;

use function json_decode;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomPendingOrder::class
)]
/**
 * @deprecated
 */
final readonly class QueryRandomPendingOrderHandler
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(QueryRandomPendingOrder $query, Deferred $promise): void
    {
        $count = 0;

        $result = null;

        while (true) {
            $order = $this->connection->table('order')->inRandomOrder()->first();

            $content = json_decode($order->content, true);

            $status = $content['order_status'] ?? null;

            if ($status === 'created') {
                $result = $content;

                break;
            }

            if ($count > 10) {
                logger('No pending order found after 10 attempts.');

                break;
            }

            $count++;
        }

        $promise->resolve($result);
    }
}
