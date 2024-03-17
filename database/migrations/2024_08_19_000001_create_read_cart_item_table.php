<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_cart_item', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cart_id');
            $table->uuid('customer_id');
            $table->uuid('sku_id');
            $table->unsignedInteger('quantity');
            $table->string('price');

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_cart_item');
    }
};
