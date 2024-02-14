<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Storm\Contract\Clock\SystemClock;

final readonly class ProductReadModel
{
    final public const TABLE = 'read_product';

    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function insert(string $skuId, string $skuCode, array $info, string $status): void
    {
        $this->connection->table(self::TABLE)->insert([
            'id' => $skuId,
            'sku_code' => $skuCode,
            'name' => $info['name'],
            'description' => $info['description'],
            'category' => $info['category'],
            'brand' => $info['brand'],
            'model' => $info['model'],
            'status' => $status,
            'created_at' => $this->clock->generate(),
        ]);
    }
}
