<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Chronicler;

use App\Chron\Package\Chronicler\Decorator\EventChronicler;
use App\Chron\Package\Chronicler\Decorator\TransactionalEventChronicler;
use App\Chron\Package\Chronicler\EventStreamProvider;
use App\Chron\Package\Chronicler\StandardStreamPersistence;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsChronicler
{
    public function __construct(
        public string $connection,
        public string $tableName = 'stream_event',
        public string $persistence = StandardStreamPersistence::class,
        public ?string $eventable = EventChronicler::class,
        public ?string $transactional = TransactionalEventChronicler::class,
        public string $evenStreamProvider = EventStreamProvider::class,
        public ?string $exceptionHandler = null,
        public ?string $abstract = null
    ) {
    }
}
