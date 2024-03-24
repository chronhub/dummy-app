<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemAdjusted;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Storm\Projector\Support\Console\ReadModelProjectionCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inventory:read-model',
    description: 'Read model for inventory'
)]
final class InventoryReadModelCommand extends ReadModelProjectionCommand
{
    protected $signature = 'inventory:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['stock' => 0, 'current_stock' => 0, 'reserved' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope
                ->ack(InventoryItemAdded::class)
                ?->incrementState('stock', $scope->event()->totalStock()->value)
                ->stack('insert', $scope->event());

            $scope
                ->ack(InventoryItemAdjusted::class)
                ?->incrementState('current_stock', -$scope->event()->quantityAdjusted()->value)
                ->stack('decrementQuantity', $scope->event()->aggregateId()->toString(), $scope->event()->quantityAdjusted()->value);

            $scope
                ->ack(InventoryItemReserved::class)
                ?->incrementState('reserved', $scope->event()->reserved()->value)
                ->stack('incrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->reserved()->value);

            $scope->ack(InventoryItemPartiallyReserved::class)
                ?->incrementState('reserved', $scope->event()->reserved()->value)
                ->stack('incrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->reserved()->value);

            $scope->ack(InventoryItemReleased::class)
                ?->updateState('reserved', -$scope->event()->released()->value, true)
                ->stack('decrementReservation', $scope->event()->aggregateId()->toString(), $scope->event()->released()->value);
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[InventoryReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'inventory';
    }

    protected function subscribeTo(): array
    {
        return ['inventory'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
