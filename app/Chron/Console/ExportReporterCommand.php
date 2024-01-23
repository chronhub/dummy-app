<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\BindReporterContainer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'reporter:export',
    description: 'Export reporter map'
)]
class ExportReporterCommand extends AbstractExporterCommand
{
    protected $signature = 'reporter:export
                            { --path=         : default to storage_app() }
                            { --name=         : default to reporter-map.extension }
                            { --extension=php : export data to json or php(array) }
                            { --force=0       : override existing file, fails if file exists }';

    protected function buildMessageMap(): array
    {
        $map = $this->laravel[BindReporterContainer::class]->getEntries();

        $data = [];
        foreach ($map as $reporterId => $reporter) {
            $data[$reporterId] = $reporter->jsonSerialize();
        }

        if ($data === []) {
            $this->components->error('No reporter found in map');

            exit(self::FAILURE);
        }

        return $data;
    }

    protected function defaultName(): string
    {
        return 'reporter-map';
    }
}
