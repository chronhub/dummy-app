<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class AttributeContainer
{
    public function __construct(
        protected BindReporterContainer $bindReporterContainer,
        protected TagHandlerContainer $tagHandlerContainer,
    ) {
    }

    public function autoWire(): void
    {
        $this->bindReporterContainer->bind();

        $this->tagHandlerContainer->tag();
    }

    public function get(string $messageName): iterable
    {
        return $this->tagHandlerContainer->find($messageName);
    }

    public function getBindings(string $key): Collection
    {
        return match ($key) {
            'reporter' => $this->bindReporterContainer->getBindings(),
            'handler' => $this->tagHandlerContainer->getBindings(),
            default => throw new InvalidArgumentException("Invalid binding key: $key, available keys: reporter, handler"),
        };
    }

    public function getEntries(string $key): Collection
    {
        return match ($key) {
            'reporter' => $this->bindReporterContainer->getEntries(),
            'handler' => $this->tagHandlerContainer->getEntries(),
            default => throw new InvalidArgumentException("Invalid entry key: $key, available keys: reporter, handler"),
        };
    }

    public function getQueues(): array
    {
        return $this->bindReporterContainer->getQueues();
    }
}
