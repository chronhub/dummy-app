<?php

declare(strict_types=1);

use App\Chron\Console\ExportMessageCommand;
use App\Chron\Console\MapListenerCommand;
use App\Chron\Console\MapMessageCommand;

return [

    /*
    |--------------------------------------------------------------------------
    | Use auto wiring
    |--------------------------------------------------------------------------
    |
    */
    'auto_wire' => true,

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
            ExportMessageCommand::class,
        ],
    ],
];
