<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Reporter\ReporterMap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @method Collection getBindings()
 * @method Collection getEntries()
 * @method array      getQueues()
 */
class BindReporterContainer
{
    use ForwardsCalls;

    public function __construct(
        protected ReporterMap $reporterMap,
        protected Application $app
    ) {
    }

    public function bind(): void
    {
        $this->reporterMap->load();
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->forwardCallTo($this->reporterMap, $method, $parameters);
    }
}
