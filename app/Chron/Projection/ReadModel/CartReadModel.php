<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Cart\CartStatus;
use App\Chron\Model\Cart\Event\CartOpened;
use Illuminate\Database\Schema\Blueprint;

final class CartReadModel extends ReadModelConnection
{
    final public const string TABLE_CART = 'read_cart';

    protected function insert(CartOpened $event): void
    {
        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'customer_id' => $event->cartOwner()->toString(),
            'status' => $event->cartStatus()->value,
        ]);
    }

    protected function update(string $cartId, string $customerId, string $balance, int $quantity): void
    {
        $this->query()
            ->where('id', $cartId)
            ->where('customer_id', $customerId)
            ->update(['balance' => $balance, 'quantity' => $quantity]);
    }

    protected function updateStatus(string $cartId, string $customerId, string $status): void
    {
        $this->query()
            ->where('id', $cartId)
            ->where('customer_id', $customerId)
            ->update(['status' => $status]);
    }

    protected function deleteSubmittedCart(string $cartOwner): void
    {
        // todo set closed status reason
        $this->query()->where('customer_id', $cartOwner)->delete();
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->enum('status', CartStatus::toStrings());
            $table->string('balance')->default('0.00');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('closed_reason')->nullable();
            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
            $table->timestampTz('closed_at', 6)->nullable();

            $table->unique(['id', 'customer_id']);
        };
    }

    protected function tableName(): string
    {
        return self::TABLE_CART;
    }
}
