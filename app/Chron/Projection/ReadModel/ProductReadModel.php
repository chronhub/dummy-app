<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Model\Product\ProductStatus;
use Illuminate\Database\Schema\Blueprint;

final class ProductReadModel extends ReadModelConnection
{
    final public const string TABLE = 'read_product';

    public function insert(ProductCreated $event): void
    {
        $info = $event->productInfo()->toArray();

        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'sku_code' => $event->skuCode(),
            'name' => $info['name'],
            'description' => $info['description'],
            'category' => $info['category'],
            'brand' => $info['brand'],
            'model' => $info['model'],
            'status' => $event->productStatus()->value,
        ]);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
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
        };
    }

    protected function tableName(): string
    {
        return self::TABLE;
    }
}
