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

        $projection = $this->make($this->reactors(), fn (): array => ['count' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope->ack(CartOpened::class)
                ?->incrementState()
                ->stack('insert', $scope->event());

            $scope->ackOneOf(
                CartItemAdded::class, CartItemPartiallyAdded::class, CartItemQuantityUpdated::class,
                CartCanceled::class, CartItemRemoved::class
            )
                ?->stack(
                    'update',
                    $scope->event()->cartId()->toString(),
                    $scope->event()->cartOwner()->toString(),
                    $scope->event()->cartBalance()->value,
                    $scope->event()->cartQuantity()->value
                );

            $scope->ackOneOf(CartCanceled::class, CartSubmitted::class)
                ?->stack('updateStatus',
                    $scope->event()->cartId()->toString(),
                    $scope->event()->cartOwner()->toString(),
                    $scope->event()->newCartStatus()->value
                );

            $scope->ack(OrderPaid::class)
                ?->stack('deleteSubmittedCart', $scope->event()->orderOwner()->toString());
        };
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
        return ['cart', 'order'];
    }

    protected function queryFilter(): ?ProjectionQueryFilter
    {
        return null;
    }
}
