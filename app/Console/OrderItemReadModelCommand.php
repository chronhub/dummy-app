<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Projection\ReadModel\OrderItemReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'order-item:read-model',
    description: 'Read model for order item'
)]
final class OrderItemReadModelCommand extends AbstractReadModelCommand
{
    protected $signature = 'order-item:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors());

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope->ack(OrderCreated::class)?->stack('insert', $scope->event());
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[OrderItemReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'order_item';
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
