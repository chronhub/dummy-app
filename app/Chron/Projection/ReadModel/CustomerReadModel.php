<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Storm\Contract\Clock\SystemClock;

final readonly class CustomerReadModel
{
    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function insert(string $customerId, string $email, string $name, string $street, string $city, string $postalCode, string $country): void
    {
        $this->query()->insert([
            'customer_id' => $customerId,
            'email' => $email,
            'name' => $name,
            'street' => $street,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country,
        ]);
    }

    public function updateEmail(string $customerId, string $email): void
    {
        $this->query()->where('customer_id', $customerId)->update(['email' => $email, 'updated_at' => $this->clock->generate()]);
    }

    private function query(): Builder
    {
        return $this->connection->table('read_customer_email');
    }
}
