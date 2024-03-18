<?php

declare(strict_types=1);

use App\Chron\Application\Console\ExportMessageCommand;
use App\Chron\Application\Console\ExportReporterCommand;
use App\Chron\Application\Console\MapListenerCommand;
use App\Chron\Application\Console\MapMessageCommand;

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
            ExportReporterCommand::class,
            \App\Chron\Application\Console\Shop\SeedShopCommand::class,
            \App\Chron\Application\Console\Shop\BatchOrderCommand::class,
        ],
    ],
];
