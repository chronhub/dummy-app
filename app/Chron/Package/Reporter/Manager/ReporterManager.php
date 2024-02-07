<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter\Manager;

use App\Chron\Package\Attribute\KernelStorage;
use Illuminate\Contracts\Foundation\Application;
use React\Promise\PromiseInterface;
use Storm\Contract\Reporter\Reporter;

final readonly class ReporterManager implements Manager
{
    public function __construct(
        private KernelStorage $storage,
        private Application $app
    ) {
    }

    public function get(string $name): Reporter
    {
        return $this->app[$name];
    }

    public function relay(array|object $message, ?string $hint = null): ?PromiseInterface
    {
        $reporter = $this->storage->getReporterByMessage($message, $hint);

        return $this->get($reporter)->relay($message);
    }
}
