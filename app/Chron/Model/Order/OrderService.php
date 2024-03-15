<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartStatus;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Model\Order\Repository\OrderList;
use RuntimeException;

final readonly class OrderService
{
    public function __construct(
        private CartList $carts,
        private OrderList $orders
    ) {
    }

    public function createOrder(string $customerId, string $cartId): void
    {
        $cartId = CartId::fromString($cartId);

        $cart = $this->carts->get($cartId);

        $this->ensureCartCanBeOrdered($customerId, $cart);

        $cart->checkout();
        $this->carts->save($cart);

        $order = Order::create($cart->cartId(), $cart->owner());
    }

    private function ensureCartCanBeOrdered(string $customerId, ?Cart $cart): Cart
    {
        if ($cart === null) {
            throw new RuntimeException('No cart found');
        }

        if ($cart->owner()->toString() !== $customerId) {
            throw new RuntimeException('Cart does not belong to customer');
        }

        if ($cart->status() !== CartStatus::OPENED) {
            throw new RuntimeException('Cart is not in a state to be ordered');
        }

        if ($cart->quantity()->isEmpty()) {
            throw new RuntimeException('Cart is empty');
        }
    }
}
