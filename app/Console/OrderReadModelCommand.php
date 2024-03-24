<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Projection\ReadModel\OrderReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Storm\Projector\Support\Console\ReadModelProjectionCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'order:read-model',
    description: 'Read model for order'
)]
final class OrderReadModelCommand extends ReadModelProjectionCommand
{
    protected $signature = 'order:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['created' => 0, 'paid' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope
                ->ack(OrderCreated::class)
                ?->incrementState('created')
                ->stack('insert', $scope->event());

            $scope
                ->ack(OrderPaid::class)
                ?->incrementState('paid')
                ->updateState('created', -1, true)
                ->stack('updateStatus', $scope->event());
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[OrderReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'order';
    }

    protected function subscribeTo(): array
    {
        return ['order'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
