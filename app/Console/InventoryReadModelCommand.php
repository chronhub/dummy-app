<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class InventoryReadModelCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'inventory:read-model';

    protected $description = 'Read model for inventory';

    private ProjectorManagerInterface $projector;

    public function __invoke(ProjectorManagerInterface $projectorManager, InventoryReadModel $readModel): int
    {
        $this->projector = $projectorManager;

        $projection = $projectorManager->newReadModelProjector('inventory', $readModel);

        $projection
            ->initialize(fn () => ['count' => 0])
            ->filter($projectorManager->queryScope()->fromIncludedPosition())
            ->subscribeToStream('inventory')
            ->when(function (ReadModelScope $scope): void {
                $scope->ack(InventoryItemAdded::class)
                    ?->incrementState()
                    ->stack('insert', $scope->event());

                $scope->ack(InventoryItemAdjusted::class)
                    ?->incrementState()
                    ->stack('updateQuantity', $scope->event()->aggregateId()->toString(), $scope->event()->totalStock()->value);

                $scope->ack(InventoryItemReserved::class)
                    ?->incrementState()
                    ->stack('incrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->reserved()->value);

                $scope->ack(InventoryItemPartiallyReserved::class)
                    ?->incrementState()
                    ->stack('incrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->reserved()->value);

                $scope->ack(InventoryItemReleased::class)
                    ?->incrementState()
                    ->stack('decrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->released()->value);
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

        $this->projector->monitor()->markAsStop('inventory');
    }
}
