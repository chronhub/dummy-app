<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Support\Collection;

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

    public function getBindings(): Collection
    {
        return $this->tagHandlerContainer->getBindings();
    }

    public function getReporterEntries(): Collection
    {
        return $this->bindReporterContainer->getEntries();
    }

    public function getHandlerEntries(): Collection
    {
        return $this->tagHandlerContainer->getEntries();
    }

    public function getQueues(): array
    {
        return $this->bindReporterContainer->getQueues();
    }
}
