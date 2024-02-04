<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Contracts;

interface ChroniclerDecorator extends Chronicler
{
    public function innerChronicler(): Chronicler;
}
