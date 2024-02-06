<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class CustomerEmailProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function isUnique(CustomerEmail $email): bool
    {
        return $this->query()->where('email', $email)->doesntExist();
    }

    public function insert(CustomerId $customerId, CustomerEmail $email): void
    {
        $this->query()->insert([
            'customer_id' => $customerId->toString(),
            'email' => $email->value,
        ]);
    }

    private function query(): Builder
    {
        return $this->connection->table('read_customer_email');
    }
}
