<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartStatus;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Model\Cart\Service\ReadCartItems;
use App\Chron\Model\Order\Factory\OrderItemsFactory;
use App\Chron\Model\Order\Repository\OrderList;
use RuntimeException;

use function sprintf;
use function usleep;

final readonly class OrderCreationProcess
{
    public function __construct(
        private CartList $carts,
        private OrderList $orders,
        private ReadCartItems $readCartItems
    ) {
    }

    public function newOrder(CartId $cartId, CartOwner $cartOwner): void
    {
        $this->checkoutCart($cartId, $cartOwner);

        usleep(5000); //fixMe: this is a workaround to

        $this->createOrder($cartId, $cartOwner);
    }

    private function checkoutCart(CartId $cartId, CartOwner $cartOwner): void
    {
        $cart = $this->carts->get($cartId);

        $this->ensureCartCanBeOrdered($cartOwner, $cart);

        $cart->checkout();

        $this->carts->save($cart);
    }

    private function createOrder(CartId $cartId, CartOwner $cartOwner): void
    {
        $orderId = OrderId::fromString($cartId->toString()); //fixMe OrderId is the same as CartId by now

        $orderItems = $this->convertCartItemsToOrderItems($cartId, $cartOwner);

        $orderOwner = OrderOwner::fromString($cartOwner->toString());

        $order = Order::create($orderId, $orderOwner, $cartId, $orderItems);

        $this->orders->save($order);
    }

    private function ensureCartCanBeOrdered(CartOwner $cartOwner, ?Cart $cart): void
    {
        if ($cart === null) {
            throw new RuntimeException(sprintf('Cart for customer id %s not found', $cartOwner->toString()));
        }

        if (! $cart->owner()->equalsTo($cartOwner)) {
            throw new RuntimeException(sprintf('Cart with id %s does not belong to customer id %s, expected customer id %s',
                $cart->cartId()->toString(),
                $cartOwner->toString(),
                $cart->owner()->toString()
            ));
        }

        if ($cart->status() !== CartStatus::OPENED) {
            throw new RuntimeException(sprintf('Cart with id %s is not in a state to be ordered, current status: %s',
                $cart->cartId()->toString(),
                $cart->status()->value));
        }

        if ($cart->quantity()->isEmpty()) {
            throw new RuntimeException(sprintf('Cart with id %s is empty', $cart->cartId()->toString()));
        }
    }

    private function convertCartItemsToOrderItems(CartId $cartId, CartOwner $cartOwner): ItemCollection
    {
        $cartItems = $this->readCartItems->get($cartId->toString(), $cartOwner->toString());

        if ($cartItems === null || $cartItems->isEmpty()) {
            throw new RuntimeException('No cart items found');
        }

        return OrderItemsFactory::make($cartItems, OrderId::fromString($cartId->toString()));
    }
}
