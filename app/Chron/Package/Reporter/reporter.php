<?php

declare(strict_types=1);

use App\Chron\Application\Console\ExportMessageCommand;
use App\Chron\Application\Console\MapListenerCommand;
use App\Chron\Application\Console\MapMessageCommand;

return [

    /*
    |--------------------------------------------------------------------------
    | Use auto binding
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
