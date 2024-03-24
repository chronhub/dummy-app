<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemPartiallyReserved;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Storm\Projector\Support\Console\ReadModelProjectionCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'catalog:read-model',
    description: 'Read model for catalog'
)]
final class CatalogReadModelCommand extends ReadModelProjectionCommand
{
    protected $signature = 'catalog:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['count' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
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

            $scope
                ->ack(InventoryItemPartiallyReserved::class)
                ?->stack('incrementReservation',
                    $scope->event()->aggregateId()->toString(),
                    $scope->event()->reserved()->value
                );

            $scope
                ->ack(InventoryItemReserved::class)
                ?->stack('incrementReservation',
                    $scope->event()->aggregateId()->toString(),
                    $scope->event()->reserved()->value
                );

            $scope
                ->ack(InventoryItemReleased::class)
                ?->stack('decrementReservation',
                    $scope->event()->aggregateId()->toString(),
                    $scope->event()->released()->value
                );
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[CatalogReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'catalog';
    }

    protected function subscribeTo(): array
    {
        return ['product', 'inventory', 'order'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
