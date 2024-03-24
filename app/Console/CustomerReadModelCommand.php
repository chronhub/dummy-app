<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Projection\ReadModel\CustomerReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Storm\Projector\Support\Console\ReadModelProjectionCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'customer:read-model',
    description: 'Read model for customer'
)]
final class CustomerReadModelCommand extends ReadModelProjectionCommand
{
    protected $signature = 'customer:read-model';

    protected ProjectorManagerInterface $projector;

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
                ->ack(CustomerRegistered::class)
                ?->incrementState()
                ->stack('insert', $scope->event());

            $scope
                ->ack(CustomerEmailChanged::class)
                ?->stack('updateEmail', $scope->event()->aggregateId()->toString(), $scope->event()->newEmail()->value);

            if ($scope->isAcked()) {
                //$this->info('Event acked:'.$scope->event()::class);
            }
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[CustomerReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'customer';
    }

    protected function subscribeTo(): array
    {
        return ['customer'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
