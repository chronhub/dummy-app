<?php

declare(strict_types=1);

use App\Chron\Model\Order\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_customer_order', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->uuid('order_id');
            $table->enum('order_status', OrderStatus::toStrings());
            $table->string('balance')->default('0.00');
            $table->boolean('closed')->default(0);
            $table->string('reason')->nullable();
            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrent();
            $table->timestampTz('closed_at', 6)->nullable();

            $table->unique(['customer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_customer_order');
    }
};
