<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Cart;

use App\Chron\Model\Cart\Event\CartItemQuantityUpdated;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CartReadModel;

final readonly class WhenCartItemQuantityUpdated
{
    public function __construct(private CartReadModel $cartReadModel)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemQuantityUpdated::class,
        priority: 0
    )]
    public function insertCartItem(CartItemQuantityUpdated $event): void
    {
        $cartItem = $event->cartItem();

        $this->cartReadModel->updateItemQuantity(
            $cartItem->id->toString(),
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $cartItem->sku->toString(),
            $cartItem->quantity->value,
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CartItemQuantityUpdated::class,
        priority: 1
    )]
    public function updateCart(CartItemQuantityUpdated $event)
    {
        $this->cartReadModel->updateCart(
            $event->cartId()->toString(),
            $event->cartOwner()->toString(),
            $event->cartBalance()->value,
            $event->cartQuantity()->value
        );
    }
}
