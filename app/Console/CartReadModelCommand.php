<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Cart\Event\CartCanceled;
use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Model\Cart\Event\CartItemPartiallyAdded;
use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Chron\Model\Cart\Event\CartOpened;
use App\Chron\Model\Cart\Event\CartSubmitted;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Projection\ReadModel\CartReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cart:read-model',
    description: 'Read model for cart'
)]
final class CartReadModelCommand extends AbstractReadModelCommand
{
    protected $signature = 'cart:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['opened' => 0, 'submitted' => 0, 'closed' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope
                ->ack(CartOpened::class)
                ?->incrementState('opened')
                ->stack('insert', $scope->event());

            $scope
                ->ack(CartSubmitted::class)
                ?->incrementState('submitted')
                ->updateState('opened', -1, true)
                ->stack('updateStatus',
                    ...$this->extractDataForUpdateStatus($scope->event())
                );

            $scope
                ->ack(OrderPaid::class)
                ?->incrementState('closed')
                ->updateState('submitted', -1, true)
                ->stack('deleteSubmittedCart', $scope->event()->orderOwner()->toString());

            $scope
                ->ack(CartCanceled::class)
                ?->stack('updateStatus',
                    ...$this->extractDataForUpdateStatus($scope->event())
                )
                ->stack('update',
                    ...$this->extractDataForUpdateEvent($scope->event())
                );

            $scope
                ->ack(CartItemAdded::class)
                ?->stack('update',
                    ...$this->extractDataForUpdateEvent($scope->event())
                );

            $scope
                ->ack(CartItemPartiallyAdded::class)
                ?->stack('update',
                    ...$this->extractDataForUpdateEvent($scope->event())
                );

            $scope
                ->ack(CartItemQuantityUpdated::class)
                ?->stack('update',
                    ...$this->extractDataForUpdateEvent($scope->event())
                );

            $scope->ack(CartItemRemoved::class
            )?->stack('update',
                ...$this->extractDataForUpdateEvent($scope->event())
            );
        };
    }

    private function extractDataForUpdateEvent(object $event): array
    {
        return [
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartBalance()->value,
            $event->cartQuantity()->value,
        ];
    }

    private function extractDataForUpdateStatus(object $event): array
    {
        return [
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->newCartStatus()->value,
        ];
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[CartReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'cart';
    }

    protected function subscribeTo(): array
    {
        return ['order', 'cart'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
