<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\MessageHandler\MessageHandlerEntry;
use App\Chron\Attribute\TagContainer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_map;
use function json_encode;
use function var_export;

#[AsCommand(
    name: 'reporter-message:export',
    description: 'Export message and message handler map'
)]
class ExportMessageCommand extends Command
{
    protected $signature = 'reporter-message:export
                            { --path=         : default to storage_app() }
                            { --name=         : default to message-map.extension }
                            { --extension=php : export data to json or php(array) }
                            { --force=0       : override existing file, fails if file exists }';

    public function __invoke(): int
    {
        $file = $this->determineFile();

        $data = $this->buildMessageMap();

        $this->exportData($file, $data);

        $replaced = File::exists($file) ? 'updated' : 'created';

        $this->components->info("File $file $replaced");

        return self::SUCCESS;
    }

    protected function exportData(string $file, array $data): void
    {
        $extension = $this->determineExtension();

        $exportTo = $extension === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : '<?php return '.var_export($data, true).';';

        File::put($file, $exportTo);
    }

    protected function buildMessageMap(): array
    {
        $map = $this->laravel[TagContainer::class]->map;

        $data = [];
        foreach ($map as $messageName => $messageHandlers) {
            $data[$messageName] = array_map(fn (MessageHandlerEntry $handler): array => $handler->jsonSerialize(), $messageHandlers);
        }

        if ($data === []) {
            $this->components->error('No messages found in map');

            exit(self::FAILURE);
        }

        return $data;
    }

    protected function determineFile(): string
    {
        $path = $this->option('path');

        if (blank($path)) {
            $path = storage_path('app');
        }

        $extension = $this->determineExtension();

        $fileName = $this->option('name') ?? 'message-map.'.$extension;

        $file = $path.'/'.$fileName;

        if (File::exists($file) && $this->option('force') !== '1') {
            $this->components->error("File $file already exists, use --force=1 to update");

            exit(self::FAILURE);
        }

        return $file;
    }

    protected function determineExtension(): string
    {
        $extension = $this->option('extension');

        if ($extension === 'json' || $extension === 'php') {
            return $extension;
        }

        $this->components->error("Extension $extension is not a valid export extension");

        exit(self::FAILURE);
    }
}
