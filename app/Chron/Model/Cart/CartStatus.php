<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

enum CartStatus: string
{
    // Default status
    case OPENED = 'opened';

    // The cart has been submitted for processing
    case SUBMITTED = 'submitted';

    // The cart has been abandoned, or otherwise not completed
    case CLOSED = 'closed';

    public static function toStrings(): array
    {
        return [
            self::OPENED->value,
            self::SUBMITTED->value,
            self::CLOSED->value,
        ];
    }
}
