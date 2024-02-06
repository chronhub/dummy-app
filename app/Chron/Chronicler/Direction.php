<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

enum Direction: string
{
    case FORWARD = 'asc';
    case BACKWARD = 'desc';
}
