<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemRemoved;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CartReadModel;

final readonly class WhenCartItemRemoved
{
    public function __construct(private CartReadModel $cartReadModel)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemRemoved::class,
        priority: 0
    )]
    public function removeCartItem(CartItemRemoved $event): void
    {
        $cartItem = $event->oldCartItem();

        $this->cartReadModel->deleteItem(
            $cartItem->id->toString(),
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $cartItem->sku->toString(),
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemRemoved::class,
        priority: 1
    )]
    public function updateCart(CartItemRemoved $event)
    {
        $this->cartReadModel->updateCart(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartBalance()->value,
            $event->cartQuantity()->value
        );
    }
}
