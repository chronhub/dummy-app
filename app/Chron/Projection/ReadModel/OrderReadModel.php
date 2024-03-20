<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Order\OrderStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

final class OrderReadModel extends ReadModelConnection
{
    public const string TABLE_ORDER = 'read_order';

    protected function insert(OrderCreated $event): void
    {
        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'customer_id' => $event->orderOwner()->toString(),
            'status' => $event->orderStatus()->value,
            'balance' => $event->orderBalance()->value(),
            'quantity' => $event->orderQuantity()->value,
        ]);
    }

    protected function updateStatus(OrderPaid $event): void
    {
        $this->query()
            ->where('id', $event->aggregateId()->toString())
            ->where('customer_id', $event->orderOwner()->toString())
            ->update([
                'status' => $event->orderStatus()->value,
            ]);
    }

    protected function query(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->enum('status', OrderStatus::toStrings());
            $table->string('balance')->default('0.00');
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('closed')->default(0); // todo remove and use status
            $table->string('reason')->nullable();
            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
            $table->timestampTz('closed_at', 6)->nullable();
        };
    }

    protected function tableName(): string
    {
        return self::TABLE_ORDER;
    }
}
