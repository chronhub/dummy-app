<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_price');
            $table->integer('stock');
            $table->integer('reserved')->default(0);

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable();
        });

        // todo add constraint to unit_price, stock, reserved
    }

    public function down(): void
    {
        Schema::dropIfExists('read_inventory');
    }
};
