<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Projection\ReadModel\ProductReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'product:read-model',
    description: 'Read model for product'
)]
final class ProductReadModelCommand extends AbstractReadModelCommand
{
    protected $signature = 'product:read-model';

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
                ->ack(ProductCreated::class)
                ?->incrementState()
                ->stack('insert', $scope->event());
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[ProductReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'product';
    }

    protected function subscribeTo(): array
    {
        return ['product'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
