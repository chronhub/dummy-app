<?php

declare(strict_types=1);

namespace Tests\Unit;

use function fnmatch;

function findApplicableReporters(array $reporters, array $supports): array
{
    $matches = [];

    foreach ($reporters as $reporter) {
        foreach ($supports as $support) {
            if (fnmatch($support, $reporter)) {
                $matches[] = $reporter;
            }
        }
    }

    return $matches;
}

it('finds applicable reporters', function (array $supports, array $expected) {
    $reporters = [
        'reporter.command.default',
        'reporter.command.generic',
        'reporter.query.default',
        'reporter.query.generic',
        'reporter.event.default',
        'reporter.event.generic',
    ];

    $matches = findApplicableReporters($reporters, $supports);

    expect($matches)->toBe($expected);
})->with([
    [
        ['*'],
        [
            'reporter.command.default',
            'reporter.command.generic',
            'reporter.query.default',
            'reporter.query.generic',
            'reporter.event.default',
            'reporter.event.generic',
        ],
    ],
    [
        ['reporter.command.*'],
        ['reporter.command.default', 'reporter.command.generic'],
    ],
    [
        ['reporter.command.*', 'reporter.query.*'],
        ['reporter.command.default', 'reporter.command.generic', 'reporter.query.default', 'reporter.query.generic'],
    ],
    [
        ['reporter.command.*', 'reporter.query.*', 'reporter.event.*'],
        ['reporter.command.default', 'reporter.command.generic', 'reporter.query.default', 'reporter.query.generic', 'reporter.event.default', 'reporter.event.generic'],
    ],
]);
