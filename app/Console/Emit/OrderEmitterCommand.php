<?php

declare(strict_types=1);

namespace App\Console\Emit;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use Closure;
use Illuminate\Console\Command;
use Storm\Contract\Projector\EmitterScope;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'order:emit',
    description: 'Emit order event'
)]
class OrderEmitterCommand extends Command
{
    protected $signature = 'order:emit';

    public function __construct(protected ProjectorManagerInterface $projectorManager)
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $projector = $this->projectorManager->newEmitterProjector('emit_order');

        $projector
            ->initialize(fn (): array => ['order-created' => 0, 'order-paid' => 0])
            ->filter($this->projectorManager->queryScope()->fromIncludedPosition())
            ->subscribeToStream('order')
            ->when($this->reactors())
            ->run(false);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (EmitterScope $scope): void {
            $scope
                ->ack(OrderCreated::class)
                ?->incrementState('order-created')
                ->linkTo('order_created', $scope->event());

            $scope
                ->ack(OrderPaid::class)
                ?->incrementState('order-paid')
                ->linkTo('order_paid', $scope->event());
        };
    }
}
