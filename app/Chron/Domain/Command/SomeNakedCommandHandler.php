<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: SomeNakedCommand::class,
)]
final class SomeNakedCommandHandler
{
    public function __invoke(SomeNakedCommand $command): void
    {
        logger('SomeNakedCommandHandler invoked');
    }
}
