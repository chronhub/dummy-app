<?php

declare(strict_types=1);

use App\Chron\Model\Cart\CartStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_cart', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->enum('status', CartStatus::toStrings());
            $table->string('balance')->default('0.00');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('closed_reason')->nullable();
            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrent();
            $table->timestampTz('closed_at', 6)->nullable();

            $table->unique(['id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_cart');
    }
};
