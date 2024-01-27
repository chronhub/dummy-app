<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Attribute\Kernel;
use Illuminate\Contracts\Foundation\Application;
use React\Promise\PromiseInterface;
use Storm\Contract\Reporter\Reporter;

final class ReporterManager implements Manager
{
    public function __construct(
        protected Kernel $attributeContainer,
        protected Application $app
    ) {
    }

    public function get(string $name): Reporter
    {
        return $this->app[$name];
    }

    public function relay(array|object $message, ?string $hint = null): ?PromiseInterface
    {
        $reporter = $this->attributeContainer->getReporterByMessageName($message, $hint);

        return $this->get($reporter)->relay($message);
    }
}
