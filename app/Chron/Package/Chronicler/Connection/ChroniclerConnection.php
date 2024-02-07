<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Connection;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class ChroniclerConnection
{
    public readonly Connection $connect;

    public function __construct(
        private Application $app,
        public readonly string $connectionName,
        public readonly string $masterTable,
    ) {
        $this->connect = $this->app['db']->connection($this->connectionName);
    }

    public function schemaBuilder(): SchemaBuilder
    {
        return $this->connect->getSchemaBuilder();
    }

    public function read(): Builder
    {
        return $this->connect->table($this->masterTable);
    }

    public function write(): Builder
    {
        return $this->connect->table($this->masterTable)->useWritePdo();
    }

    public function connectionName(): string
    {
        return $this->connectionName;
    }

    public function getConnection(): Connection
    {
        return $this->connect;
    }
}
