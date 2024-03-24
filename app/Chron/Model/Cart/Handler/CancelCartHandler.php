<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\CancelCart;
use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartItemsManager;
use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\Cart\Repository\CartList;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.async.default',
    handles: CancelCart::class,
)]
final readonly class CancelCartHandler
{
    public function __construct(
        private CartList $cartList,
        private CartItemsManager $cartItemsManager,
    ) {
    }

    public function __invoke(CancelCart $command): void
    {
        $cart = $this->cartList->get($command->cartId());

        if (! $cart instanceof Cart) {
            throw CartNotFound::withCartId($command->cartId());
        }

        $cart->cancel($this->cartItemsManager);

        $this->cartList->save($cart);
    }
}
