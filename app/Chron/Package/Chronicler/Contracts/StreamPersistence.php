<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Contracts;

use Storm\Stream\Stream;

interface StreamPersistence
{
    public function serialize(Stream $stream): array;
}
