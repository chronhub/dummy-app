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
            $table->enum('status', OrderStatus::toStrings());
            $table->timestampsTz(6);

            $table->unique(['customer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_customer_order');
    }
};
