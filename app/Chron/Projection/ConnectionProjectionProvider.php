<?php

declare(strict_types=1);

namespace App\Chron\Projection;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use stdClass;
use Storm\Contract\Clock\SystemClock;
use Storm\Contract\Projector\ProjectionData;
use Storm\Contract\Projector\ProjectionModel;
use Storm\Contract\Projector\ProjectionProvider;
use Storm\Projector\Exception\ProjectionAlreadyExists;
use Storm\Projector\Exception\ProjectionAlreadyRunning;
use Storm\Projector\Exception\ProjectionNotFound;
use Storm\Projector\Repository\Data\CreateData;
use Storm\Projector\Repository\Data\StartData;

use function sprintf;

final readonly class ConnectionProjectionProvider implements ProjectionProvider
{
    public const string TABLE = 'projections';

    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function createProjection(string $projectionName, ProjectionData $data): void
    {
        if (! $data instanceof CreateData) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        if ($this->exists($projectionName)) {
            throw ProjectionAlreadyExists::withName($projectionName);
        }

        $this->query()->insert([
            'name' => $projectionName,
            'status' => $data->status,
            'state' => '{}',
            'checkpoint' => '{}',
            'locked_until' => null,
        ]);
    }

    public function acquireLock(string $projectionName, ProjectionData $data): void
    {
        if (! $data instanceof StartData) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        $success = $this->query()
            ->where('name', $projectionName)
            ->where(function (Builder $query): void {
                $query->whereRaw('locked_until IS NULL OR locked_until < ?', [$this->clock->generate()]);
            })->update([
                'status' => $data->status,
                'locked_until' => $data->lockedUntil,
            ]);

        if ($success === 0) {
            $this->assertProjectionExists($projectionName);

            throw ProjectionAlreadyRunning::withName($projectionName);
        }
    }

    public function updateProjection(string $projectionName, ProjectionData $data): void
    {
        $success = $this->query()->where('name', $projectionName)->update($data->toArray());

        if ($success === 0) {
            $this->assertProjectionExists($projectionName);

            throw new ConnectionProjectionFailed(sprintf('Failed to update projection with name %s', $projectionName));
        }
    }

    public function deleteProjection(string $projectionName): void
    {
        $success = $this->query()->where('name', $projectionName)->delete();

        if ($success === 0) {
            $this->assertProjectionExists($projectionName);

            throw new ConnectionProjectionFailed(sprintf('Failed to delete projection with name %s', $projectionName));
        }
    }

    public function retrieve(string $projectionName): ?ProjectionModel
    {
        $projection = $this->query()->where('name', $projectionName)->first();

        if (! $projection instanceof stdClass) {
            return null;
        }

        return new Projection(
            $projection->name,
            $projection->status,
            $projection->state,
            $projection->checkpoint,
            $projection->locked_until
        );
    }

    public function filterByNames(string ...$projectionNames): array
    {
        return $this->query()->whereIn('name', $projectionNames)->pluck('name')->toArray();
    }

    public function exists(string $projectionName): bool
    {
        return $this->query()->where('name', $projectionName)->exists();
    }

    private function assertProjectionExists(string $projectionName): void
    {
        if (! $this->exists($projectionName)) {
            throw ProjectionNotFound::withName($projectionName);
        }
    }

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
