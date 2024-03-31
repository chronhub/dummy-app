<?php

declare(strict_types=1);

namespace App;

use Storm\Contract\Chronicler\StreamEventConverter;
use Storm\Contract\Message\MessageFactory;
use Storm\Contract\Serializer\MessageSerializer;
use Storm\Contract\Serializer\StreamEventSerializer;

class OctaneStorm
{
    public static function warm(): array
    {
        return [
            //            'chronicler.event.transactional.standard.pgsql',
            //            MessageSerializer::class,
            //            MessageFactory::class,
            //            'message.factory.default',
            //            'message.decorator.chain.default',
            //            'event.publisher.in_memory',
            //            StreamEventSerializer::class,
            //            StreamEventConverter::class,
            //            'event.decorator.chain.default',
        ];
    }
}
