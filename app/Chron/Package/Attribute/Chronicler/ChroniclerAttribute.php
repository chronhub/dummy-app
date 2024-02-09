<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Chronicler;

class ChroniclerAttribute
{
    public function __construct(
        public string $chronicler,
        public string $connection,
        public string $tableName,
        public string $persistence,
        public bool $eventable,
        public bool $transactional,
        public string $evenStreamProvider,
        public string $streamEventLoader,
        public string $abstract,
        public array $subscribers,
        public string $decoratorFactory,
    ) {
    }
}
