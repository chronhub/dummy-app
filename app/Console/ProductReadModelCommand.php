<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Projection\ReadModel\ProductReadModel;
use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Command\SignalableCommandInterface;

use function pcntl_async_signals;

final class ProductReadModelCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'product:read-model';

    protected $description = 'Read model for product';

    protected ProjectorManagerInterface $projector;

    public function __invoke(ProjectorManagerInterface $projectorManager, ProductReadModel $readModel): int
    {
        $this->projector = $projectorManager;

        pcntl_async_signals(true);

        $projection = $projectorManager->newReadModelProjector('product', $readModel);

        $projection
            ->initialize(fn () => ['count' => 0])
            ->filter($projectorManager->queryScope()->fromIncludedPosition())
            ->subscribeToStream('product')
            ->when(function (ReadModelScope $scope): void {
                $scope
                    ->ack(ProductCreated::class)
                    ?->incrementState()
                    ->stack('insert', $scope->event());
            })
            ->run(true);

        return self::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal)
    {
        $this->info('Signal received:'.$signal);

        $this->projector->monitor()->markAsStop('product');
    }
}
