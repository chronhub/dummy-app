<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Cart\Event\CartCanceled;
use App\Chron\Model\Cart\Event\CartItemAdded;
use App\Chron\Model\Cart\Event\CartItemPartiallyAdded;
use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Projection\ReadModel\CartItemReadModel;
use Closure;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ReadModel;
use Storm\Contract\Projector\ReadModelScope;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cart-item:read-model',
    description: 'Read model for cart item'
)]
final class CartItemReadModelCommand extends AbstractReadModelCommand
{
    protected $signature = 'cart-item:read-model';

    public function __invoke(): int
    {
        $projection = $this->make($this->reactors(), fn (): array => ['count' => 0]);

        $projection->run(true);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (ReadModelScope $scope): void {
            $scope->ackOneOf(CartItemAdded::class, CartItemPartiallyAdded::class)
                ?->incrementState()
                ->stack('insert', $scope->event());

            $scope->ack(CartItemRemoved::class)
                ?->stack(
                    'deleteOne',
                    $scope->event()->oldCartItem()->id->toString(),
                    $scope->event()->cartId()->toString(),
                    $scope->event()->cartOwner()->toString(),
                    $scope->event()->oldCartItem()->sku->toString()
                );

            $scope->ackOneOf(CartCanceled::class)
                ?->stack(
                    'deleteAll',
                    $scope->event()->cartId()->toString(),
                    $scope->event()->cartOwner()->toString()
                );

            $scope->ack(CartItemQuantityUpdated::class)
                ?->stack('updateQuantity', $scope->event());

            //wip
            $scope->ack(OrderPaid::class)
                ?->incrementState()
                ->stack('deleteSubmitted', $scope->event()->orderOwner()->toString());
        };
    }

    protected function readModel(): ReadModel
    {
        return $this->laravel[CartItemReadModel::class];
    }

    protected function projectionName(): string
    {
        return 'cart_item';
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
