<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\KernelStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function json_encode;
use function str_ends_with;
use function var_export;

abstract class AbstractExporterCommand extends Command
{
    public function __invoke(): int
    {
        $file = $this->determineFile();

        $data = $this->buildMessageMap();

        $replaced = File::isFile($file) ? 'updated' : 'created';

        $this->exportData($file, $data);

        $this->components->info("File $file $replaced");

        return self::SUCCESS;
    }

    abstract protected function buildMessageMap(): array;

    abstract protected function defaultName(): string;

    protected function exportData(string $file, array $data): void
    {
        $extension = $this->determineExtension();

        $exportTo = $extension === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : '<?php return '.var_export($data, true).';';

        File::put($file, $exportTo);
    }

    protected function determineFile(): string
    {
        $path = $this->option('path');

        if (blank($path)) {
            $path = storage_path('app');
        }

        $this->assertPathExists($path);

        $extension = $this->determineExtension();

        $fileName = $this->determineName().$extension;

        $file = $path.'/'.$fileName;

        if (File::isFile($file) && $this->option('force') !== '1') {
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

    protected function determineName(): string
    {
        $name = $this->option('name');

        if (blank($name)) {
            $name = $this->defaultName();
        }

        if (! str_ends_with($name, '.')) {
            $name .= '.';
        }

        return $name;
    }

    protected function assertPathExists(bool|array|string|null $path): void
    {
        if (! File::exists($path)) {
            $this->components->error("Path $path does not exist");

            exit(self::FAILURE);
        }
    }

    protected function kernel(): KernelStorage
    {
        return $this->laravel[KernelStorage::class];
    }
}
