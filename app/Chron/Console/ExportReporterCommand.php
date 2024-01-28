<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\Reporter\ReporterAttribute;
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
        $entries = $this->kernel()
            ->reporting()
            ->map(fn (ReporterAttribute $attribute): array => $attribute->jsonSerialize())->toArray();

        if ($entries === []) {
            $this->components->error('No reporter found in map');

            exit(self::FAILURE);
        }

        return $entries;
    }

    protected function defaultName(): string
    {
        return 'reporter-map';
    }
}
