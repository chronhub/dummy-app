<?php

declare(strict_types=1);

namespace App\Chron\Domain\Ag\Concerns;

use App\Chron\Domain\Ag\DomainEvent;
use Generator;
use Symfony\Component\Uid\Uuid;

use function end;
use function explode;

trait HasAggregateBehaviour
{
    private int $version = 0;

    /**
     * @var array<DomainEvent>
     */
    private array $recordedEvents = [];

    protected function __construct(private readonly Uuid $aggregateId)
    {
    }

    public function aggregateId(): Uuid
    {
        return $this->aggregateId;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $releasedEvents;
    }

    public static function reconstitute(Uuid $aggregateId, Generator $events): ?static
    {
        $aggregateRoot = new static($aggregateId);

        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRoot->version = (int) $events->getReturn();

        return $aggregateRoot->version() > 0 ? $aggregateRoot : null;
    }

    protected function recordThat(DomainEvent $event): void
    {
        $this->apply($event);

        $this->recordedEvents[] = $event;
    }

    protected function apply(DomainEvent $event): void
    {
        $parts = explode('\\', $event::class);

        $this->{'apply'.end($parts)}($event);

        $this->version++;
    }
}
