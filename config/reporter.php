<?php

declare(strict_types=1);

use Storm\Contract\Reporter\Reporter;

return [

    'reporter' => [
        'command' => [
            'default' => [
                'reporter' => 'reporter.command.default',
                //'class' => \Storm\Reporter\ReportCommand::class, // optional (default)
                //'tracker' => \Storm\Tracker\TrackMessage::class, // optional, class or id
                'subscribers' => [
                    Reporter::DISPATCH_EVENT => [
                        [\Storm\Reporter\Subscriber\HandleCommand::class, 0],
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
                        [\Storm\Reporter\Subscriber\HandleEvent::class, 0],
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
                        [\Storm\Reporter\Subscriber\HandleQuery::class, 0],
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
            [\Storm\Reporter\Subscriber\MakeMessage::class, 100000],
            [\Storm\Support\Message\MessageDecoratorSubscriber::class, 90000],
            [\App\Chron\Reporter\Subscribers\MessageQueueSubscriber::class, 40000],
            [\App\Chron\Reporter\Subscribers\RouteMessageSubscriber::class, 10000],
        ],

        Reporter::FINALIZE_EVENT => [

        ],

        'listeners' => [
            // your listeners here ...
        ],
    ],

    'console' => [
        'commands' => \App\Chron\Console\MessageMapCommand::class,
    ],
];
