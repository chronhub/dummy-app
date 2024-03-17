<?php

declare(strict_types=1);

use App\Chron\Model\Customer\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_customer', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('gender', Gender::toStrings());
            $table->date('birthday');
            $table->string('email')->unique();
            $table->string('phone_number', 20); //unique
            $table->string('street');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country');

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_customer');
    }
};
