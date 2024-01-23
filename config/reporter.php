<?php

declare(strict_types=1);

use App\Chron\Console\ExportMessageCommand;
use App\Chron\Console\ExportReporterCommand;
use App\Chron\Console\MapListenerCommand;
use App\Chron\Console\MapMessageCommand;

return [
    /*
    |--------------------------------------------------------------------------
    | Console commands
    |--------------------------------------------------------------------------
    |
    */
    'console' => [
        'commands' => [
            MapMessageCommand::class,
            MapListenerCommand::class,
            ExportReporterCommand::class,
            ExportMessageCommand::class,
        ],
    ],
];
