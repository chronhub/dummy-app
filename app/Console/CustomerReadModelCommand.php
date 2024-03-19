<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Projection\ReadModel\CustomerReadModel;
use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Command\SignalableCommandInterface;

use function pcntl_async_signals;

final class CustomerReadModelCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'customer:read-model';

    protected $description = 'Read model for customer';

    protected ProjectorManagerInterface $projector;

    public function __invoke(ProjectorManagerInterface $projectorManager, CustomerReadModel $readModel): int
    {
        $this->projector = $projectorManager;

        pcntl_async_signals(true);

        $projection = $projectorManager->newReadModelProjector('customer', $readModel);

        $projection
            ->initialize(fn () => ['count' => 0])
            ->filter($projectorManager->queryScope()->fromIncludedPosition())
            ->subscribeToStream('customer')
            ->when(function (ReadModelScope $scope): void {
                $scope
                    ->ack(CustomerRegistered::class)
                    ?->incrementState()
                    ->stack('insert', $scope->event());

                $scope
                    ->ack(CustomerEmailChanged::class)
                    ?->incrementState()
                    ->stack('updateEmail', $scope->event()->aggregateId()->toString(), $scope->event()->newEmail()->value);

                if ($scope->isAcked()) {
                    //$this->info('Event acked:'.$scope->event()::class);
                }
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
        $this->projector->monitor()->markAsStop('customer');
    }
}
