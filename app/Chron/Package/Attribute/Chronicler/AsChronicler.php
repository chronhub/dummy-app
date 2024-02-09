<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Chronicler;

use App\Chron\Package\Chronicler\CursorConnectionLoader;
use App\Chron\Package\Chronicler\EventStreamProvider;
use App\Chron\Package\Chronicler\StandardStreamPersistence;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsChronicler
{
    public function __construct(
        public string $connection,
        public string $abstract,
        public string $persistence = StandardStreamPersistence::class,
        public string $evenStreamProvider = EventStreamProvider::class,
        public string $streamEventLoader = CursorConnectionLoader::class,
        public bool $eventable = true,
        public bool $transactional = true,
        public string $decoratorFactory = ChroniclerDecoratorFactory::class,
        /**
         * string as invokable service where the instance of ES and stream tracker is injected
         * array as a list of subscriber, they must return an instance of StreamListener
         *
         * @var string|array
         */
        public string|array $subscribers = [],
        public string $tableName = 'stream_event', // todo: move to config
    ) {
    }
}
