<?php

declare(strict_types=1);

use App\Chron\Console\ExportMessageCommand;
use App\Chron\Console\MapListenerCommand;
use App\Chron\Console\MapMessageCommand;
use App\Chron\Reporter\QueueOption;
use App\Chron\Reporter\Subscribers\RouteMessageSubscriber;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleEvent;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Support\Message\MessageDecoratorSubscriber;

return [

    'reporter' => [
        'command' => [
            'default' => [
                'reporter' => 'reporter.command.default',
                //'class' => \Storm\Reporter\ReportCommand::class, // optional (default)
                //'tracker' => \Storm\Tracker\TrackMessage::class, // optional, class or id
                'subscribers' => [
                    Reporter::DISPATCH_EVENT => [
                        [HandleCommand::class, 0],
                    ],
                    Reporter::FINALIZE_EVENT => [

                    ],
                    'listeners' => [
                        // your listeners here ...
                    ],
                ],

                //'queue' ...

            ],
        ],
        'event' => [
            'default' => [
                'reporter' => 'reporter.event.default',
                //'class' => \Storm\Reporter\ReportEvent::class, // optional (default)
                //'tracker' => \Storm\Tracker\TrackMessage::class, // optional, class or id
                'subscribers' => [
                    Reporter::DISPATCH_EVENT => [
                        [HandleEvent::class, 0],
                    ],
                    Reporter::FINALIZE_EVENT => [

                    ],
                    'listeners' => [
                        // your listeners here ...
                    ],
                ],

                //'queue' ...
            ],
        ],
        'query' => [
            'default' => [
                'reporter' => 'reporter.query.default',
                //'class' => \Storm\Reporter\ReportQuery::class, // optional (default)
                //'tracker' => \Storm\Tracker\TrackMessage::class, // optional, class or id
                'subscribers' => [
                    Reporter::DISPATCH_EVENT => [
                        [HandleQuery::class, 0],
                    ],
                    Reporter::FINALIZE_EVENT => [

                    ],
                    'listeners' => [
                        // your listeners here ...
                    ],
                ],

                // No queue for queries
            ],
        ],
    ],

    /**
     * Default subscribers for all reporters.
     */
    'subscribers' => [
        Reporter::DISPATCH_EVENT => [
            [MakeMessage::class, 100000],
            [MessageDecoratorSubscriber::class, 90000], // stub message decorator
            [RouteMessageSubscriber::class, 10000],
        ],

        Reporter::FINALIZE_EVENT => [

        ],

        'listeners' => [
            // your listeners here ...
        ],
    ],

    /**
     * Per default, command and event reporters are sync.
     *
     * Note that 'async' has effect only when set in reporter config.
     *
     * @see \App\Chron\Attribute\MessageHandler\AsMessageHandler
     */
    'queue' => [
        'default' => QueueOption::class,
        'async' => false,
    ],

    /**
     * Console commands.
     */
    'console' => [
        'commands' => [
            MapMessageCommand::class,
            MapListenerCommand::class,
            ExportMessageCommand::class,
        ],
    ],
];
