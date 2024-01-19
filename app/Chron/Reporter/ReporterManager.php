<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Storm\Contract\Reporter\Reporter;

use function is_string;

final class ReporterManager extends AbstractReporterManager
{
    /**
     * @var array<string,string>
     */
    protected array $defaults = [];

    /**
     * @var array<string,Reporter>
     */
    protected array $reporters = [];

    public function create(string $name, string|DomainType $type): Reporter
    {
        $type = is_string($type) ? DomainType::from($type) : $type;

        return $this->reporters[$name] ??= $this->resolve($name, $type);
    }

    public function command(?string $name = null): Reporter
    {
        return $this->create($name ?? $this->defaults['command'], DomainType::COMMAND);
    }

    public function event(?string $name = null): Reporter
    {
        return $this->create($name ?? $this->defaults['event'], DomainType::EVENT);
    }

    public function query(?string $name = null): Reporter
    {
        return $this->create($name ?? $this->defaults['query'], DomainType::QUERY);
    }

    public function getDefaultId(string $type): string
    {
        return $this->defaults[$type];
    }

    public function addDefaults(string $type, string $id): void
    {
        $this->defaults[$type] = $id;
    }
}
