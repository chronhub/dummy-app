<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter;

enum DomainType: string
{
    case COMMAND = 'command';

    case EVENT = 'event';

    case QUERY = 'query';
}
