<?php

declare(strict_types=1);

use App\Chron\Model\Product\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_product', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('sku_code')->unique();
            $table->string('name');
            $table->string('description');
            $table->string('category');
            $table->string('brand');
            $table->string('model');
            $table->enum('status', ProductStatus::toStrings());

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_product');
    }
};
