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
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inventory:read-model',
    description: 'Read model for inventory'
)]
final class InventoryReadModelCommand extends AbstractReadModelCommand
{
    protected $signature = 'inventory:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['count' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
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
