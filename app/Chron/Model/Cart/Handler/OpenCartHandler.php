<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\OpenCart;
use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\Exception\CartAlreadyExists;
use App\Chron\Model\Cart\Repository\CartList;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: OpenCart::class,
)]
final readonly class OpenCartHandler
{
    public function __construct(private CartList $cartList)
    {
    }

    public function __invoke(OpenCart $command): void
    {
        if ($this->cartList->get($command->cartId()) !== null) {
            throw CartAlreadyExists::withCartId($command->cartId());
        }

        $cart = Cart::open($command->cartId(), $command->customerId());

        $this->cartList->save($cart);
    }
}
