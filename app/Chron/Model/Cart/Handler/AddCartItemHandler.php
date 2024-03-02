<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartItem;
use App\Chron\Model\Cart\CartItemId;
use App\Chron\Model\Cart\CartItemsManager;
use App\Chron\Model\Cart\Exception\CartNotFound;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: AddCartItem::class,
)]
final readonly class AddCartItemHandler
{
    public function __construct(
        private CartList $cartList,
        private CartItemsManager $cartItemsManager,
    ) {
    }

    public function __invoke(AddCartItem $command): void
    {
        $cart = $this->cartList->get($command->cartId());

        if (! $cart instanceof Cart) {
            throw CartNotFound::withCartId($command->cartId());
        }

        $cartItem = $this->makeCartItem($command->content);

        $cart->addItem($cartItem, $this->cartItemsManager);

        $this->cartList->save($cart);
    }

    private function makeCartItem(array $content): CartItem
    {
        $cartItemId = CartItemId::create();

        return CartItem::make($cartItemId, $content);
    }
}
