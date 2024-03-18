<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

enum Gender: string
{
    case FEMALE = 'female';

    case MALE = 'male';

    case OTHER = 'other';

    public static function toStrings(): array
    {
        return [
            self::FEMALE->value,
            self::MALE->value,
            self::OTHER->value,
        ];
    }
}
