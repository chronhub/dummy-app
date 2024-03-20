<?php

declare(strict_types=1);

namespace App\Chron\Projection;

use Override;
use Storm\Contract\Projector\CheckpointRecognition;
use Storm\Contract\Projector\ProjectionOption;
use Storm\Contract\Projector\ProjectionRepository;
use Storm\Projector\Checkpoint\CheckpointCollection;
use Storm\Projector\Checkpoint\CheckpointInMemory;
use Storm\Projector\Checkpoint\CheckpointManager;
use Storm\Projector\Checkpoint\NoopGapDetector;
use Storm\Projector\Factory\AbstractSubscriptionFactory;
use Storm\Projector\Repository\InMemoryRepository;

final class ConnectionSubscriptionFactory extends AbstractSubscriptionFactory
{
    private bool $useEvents = false;

    #[Override]
    public function useEvents(bool $useEvents): void
    {
        $this->useEvents = $useEvents;
    }

    #[Override]
    protected function createProjectionRepository(string $streamName, ProjectionOption $options): ProjectionRepository
    {
        $repository = new InMemoryRepository(
            $this->projectionProvider,
            $this->createLockManager($options),
            $this->jsonSerializer,
            $streamName
        );

        if (! $this->useEvents) {
            return $repository;
        }

        return $this->createDispatcherRepository($repository);
    }

    #[Override]
    protected function createCheckpointRecognition(ProjectionOption $option, bool $detectGap): CheckpointRecognition
    {
        $checkpoints = new CheckpointCollection($this->clock);

        if ($detectGap) {
            return new CheckpointManager(
                $checkpoints,
                new NoopGapDetector()
            );
        }

        return new CheckpointInMemory($checkpoints);
    }
}
