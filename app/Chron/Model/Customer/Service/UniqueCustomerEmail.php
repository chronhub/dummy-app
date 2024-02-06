<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;
use Illuminate\Database\Connection;

final readonly class UniqueCustomerEmail
{
    public function __construct(private Connection $connection)
    {
    }

    // tmp till projection
    public function isUnique(CustomerEmail $email): bool
    {
        return $this->connection->table('customer')
            ->whereJsonContains('content->customer_email', $email->value)
            ->orWhereJsonContains('content->customer_old_email', $email->value)
            ->orWhereJsonContains('content->customer_new_email', $email->value)
            ->doesntExist();
    }
}
