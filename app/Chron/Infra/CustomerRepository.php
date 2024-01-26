<?php

declare(strict_types=1);

namespace App\Chron\Infra;

use Illuminate\Database\Connection;
use stdClass;

use function json_decode;
use function json_encode;

final readonly class CustomerRepository
{
    public function __construct(
        private TransactionalDispatcher $dispatch,
        private Connection $connection,
    ) {
    }

    public function createCustomer(string $id, array $headers, array $content): void
    {
        $data = [
            'stream_name' => 'customer',
            'id' => $id,
            'type' => 'aggregate_customer',
            'version' => 1,
            'metadata' => json_encode($headers),
            'content' => json_encode($content),
        ];

        $this->connection->table('stream_event')->insert($data);
    }

    public function updateRandomCustomerEmail(array $headers): void
    {
        $customer = $this->oneRandomCustomer();

        $content = json_decode($customer->content);

        $data = [
            'stream_name' => 'customer',
            'id' => $customer->id,
            'type' => 'aggregate_customer',
            'version' => $customer->version + 1,
            'metadata' => json_encode($headers),
            'content' => json_encode([
                'customer_id' => $customer->id,
                'customer_old_email' => $content->customer_email,
                'customer_email' => fake()->email,
            ]),
        ];

        $this->connection->table('stream_event')->insert($data);
    }

    public function oneRandomCustomer(): stdClass
    {
        $customer = $this->connection->table('customer')->inRandomOrder()->first(['id']);

        return $this->connection->table('customer')
            ->where('id', $customer->id)
            ->orderBy('version', 'desc')
            ->first();
    }

    public function oneRandomCustomerFromView(): stdClass
    {
        return $this->connection->table('last_version_aggregate_view')
            ->where('type', 'aggregate_customer')
            ->inRandomOrder()
            ->first();
    }

    public function customerById(string $id): stdClass
    {
        return $this->connection->table('customer')
            ->where('id', $id)
            ->orderBy('version', 'desc')
            ->first();
    }

    public function customerByIdFromView(string $id): stdClass
    {
        return $this->connection
            ->table('last_version_aggregate_view')
            ->where('id', $id)
            ->where('type', 'aggregate_customer')
            ->first();
    }
}
