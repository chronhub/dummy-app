<?php

declare(strict_types=1);

use App\Chron\Console\ExportMessageCommand;
use App\Chron\Console\MapListenerCommand;
use App\Chron\Console\MapMessageCommand;
use App\Chron\Reporter\Subscribers\MessageQueueSubscriber;
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
            ],
        ],
    ],

    'subscribers' => [
        Reporter::DISPATCH_EVENT => [
            [MakeMessage::class, 100000],
            [MessageDecoratorSubscriber::class, 90000], // a stub message decorator
            [MessageQueueSubscriber::class, 40000],
            [RouteMessageSubscriber::class, 10000],
        ],

        Reporter::FINALIZE_EVENT => [

        ],

        'listeners' => [
            // your listeners here ...
        ],
    ],

    'console' => [
        'commands' => [
            MapMessageCommand::class,
            MapListenerCommand::class,
            ExportMessageCommand::class,
        ],
    ],
];
