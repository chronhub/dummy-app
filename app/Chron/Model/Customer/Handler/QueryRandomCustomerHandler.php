<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Query\QueryRandomCustomer;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use Illuminate\Database\Connection;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomCustomer::class
)]
final readonly class QueryRandomCustomerHandler
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(QueryRandomCustomer $query, Deferred $promise): void
    {
        $customer = $this->connection->table('customer')->inRandomOrder()->first();

        $promise->resolve($customer);
    }
}
