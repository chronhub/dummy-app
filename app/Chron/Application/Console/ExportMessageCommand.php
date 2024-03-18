<?php

declare(strict_types=1);

namespace App\Chron\Application\Console;

use Storm\Message\Attribute\MessageAttribute;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_map;

#[AsCommand(
    name: 'reporter-message:export',
    description: 'Export message and message handler map'
)]
class ExportMessageCommand extends AbstractExporterCommand
{
    protected $signature = 'reporter-message:export
                            { --path=         : default to storage_app() }
                            { --name=         : default to message-map.extension }
                            { --extension=php : export data to json or php(array) }
                            { --force=0       : override existing file, fails if file exists }';

    protected function buildMessageMap(): array
    {
        $entries = $this->kernel()->getMessages();

        $data = [];
        foreach ($entries as $messageName => $messageHandlers) {
            $data[$messageName] = array_map(fn (MessageAttribute $handler): array => $handler->jsonSerialize(), $messageHandlers);
        }

        if ($data === []) {
            $this->components->error('No message found in map');

            exit(self::FAILURE);
        }

        return $data;
    }

    protected function defaultName(): string
    {
        return 'reporter-message-map';
    }
}
