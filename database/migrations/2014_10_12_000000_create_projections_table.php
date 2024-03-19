<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projections', function (Blueprint $table) {
            $table->bigInteger('no', true);
            $table->string('name', 150)->unique();
            $table->json('state');
            $table->json('checkpoint');
            $table->string('status', 28);
            $table->timestampTz('locked_until', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projections');
    }
};
