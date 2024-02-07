<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler;

enum Direction: string
{
    case FORWARD = 'asc';
    case BACKWARD = 'desc';
}
