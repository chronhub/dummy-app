<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use Illuminate\Console\Command;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class CatalogReadModelCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'catalog:read-model';

    protected $description = 'Read model for catalog';

    protected ProjectorManagerInterface $projector;

    public function __invoke(ProjectorManagerInterface $projectorManager, CatalogReadModel $readModel): int
    {
        $this->projector = $projectorManager;

        $projection = $projectorManager->newReadModelProjector('catalog', $readModel);

        $projection
            ->initialize(fn () => ['count' => 0])
            ->filter($projectorManager->queryScope()->fromIncludedPosition())
            ->subscribeToStream('product', 'inventory', 'order')
            ->when(function (ReadModelScope $scope): void {
                $scope
                    ->ack(OrderPaid::class)
                    ?->stack('removeProductQuantity', $scope->event()->orderItems());

                $scope
                    ->ack(ProductCreated::class)
                    ?->incrementState()
                    ->stack('insert', $scope->event());

                $scope
                    ->ack(InventoryItemAdded::class)
                    ?->stack('updateProductQuantityAndPrice', $scope->event());

                if ($scope->ackOneOf(
                    InventoryItemAdjusted::class, InventoryItemPartiallyReserved::class,
                    InventoryItemReserved::class, InventoryItemReleased::class)) {
                    $scope->stack('updateReservation',
                        $scope->event()->aggregateId()->toString(),
                        $scope->event()->totalReserved()->value
                    );
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
        $this->info('Signal received:'.$signal);

        $this->projector->monitor()->markAsStop('catalog');
    }
}
