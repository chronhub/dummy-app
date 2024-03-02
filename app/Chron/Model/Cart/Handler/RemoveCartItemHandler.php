<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\RemoveCartItem;
use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartItemsManager;
use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: RemoveCartItem::class,
)]
final readonly class RemoveCartItemHandler
{
    public function __construct(
        private CartList $cartList,
        private CartItemsManager $cartItemsManager,
    ) {
    }

    public function __invoke(RemoveCartItem $command): void
    {
        $cart = $this->cartList->get($command->cartId());

        if (! $cart instanceof Cart) {
            throw CartNotFound::withCartId($command->cartId());
        }

        $cart->removeItem($command->cartItemId(), $command->cartItemSku(), $this->cartItemsManager);

        $this->cartList->save($cart);
    }
}
