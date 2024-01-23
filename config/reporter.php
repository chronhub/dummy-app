<?php

declare(strict_types=1);

use App\Chron\Console\ExportExporterCommand;
use App\Chron\Console\ExportMessageCommand;
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
            ExportExporterCommand::class,
            ExportMessageCommand::class,
        ],
    ],
];
