<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartStatus;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Model\Cart\Service\ReadCartItems;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Inventory\UnitPrice;
use App\Chron\Model\Order\Repository\OrderList;
use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;

final readonly class OrderDomainService
{
    public function __construct(
        private CartList $carts,
        private OrderList $orders,
        private ReadCartItems $readCartItems
    ) {
    }

    public function createOrder(CartId $cartId, CartOwner $cartOwner): void
    {
        $cart = $this->carts->get($cartId);

        $this->ensureCartCanBeOrdered($cartOwner, $cart);

        $cart->checkout();
        $this->carts->save($cart);

        $orderId = OrderId::fromString($cartId->toString());

        $orderItems = $this->makeOrderItems($this->getCartItems($cartId, $cartOwner), $orderId);

        $order = Order::create($orderId, OrderOwner::fromString($cartOwner->toString()), $orderItems);

        $this->orders->save($order);
    }

    private function ensureCartCanBeOrdered(CartOwner $cartOwner, ?Cart $cart): void
    {
        if ($cart === null) {
            throw new RuntimeException('No cart found');
        }

        if (! $cart->owner()->equalsTo($cartOwner)) {
            throw new RuntimeException('Cart does not belong to customer');
        }

        if ($cart->status() !== CartStatus::OPENED) {
            throw new RuntimeException('Cart is not in a state to be ordered, current status: '.$cart->status()->value);
        }

        if ($cart->quantity()->isEmpty()) {
            throw new RuntimeException('Cart is empty');
        }
    }

    private function getCartItems(CartId $cartId, CartOwner $cartOwner): Collection
    {
        $cartItems = $this->readCartItems->get($cartId->toString(), $cartOwner->toString());

        if ($cartItems === null || $cartItems->isEmpty()) {
            throw new RuntimeException('No cart items found');
        }

        return $cartItems;
    }

    private function makeOrderItems(Collection $cartItems, OrderId $orderId): ItemCollection
    {
        $items = new ItemCollection($orderId);

        $cartItems->each(function (stdClass $cartItem) use ($items) {
            $orderItem = OrderItem::fromValues(
                OrderItemId::fromString($cartItem->id),
                SkuId::fromString($cartItem->sku_id),
                UnitPrice::create($cartItem->price),
                Quantity::create($cartItem->quantity)
            );

            $items->put($orderItem);
        });

        return $items;
    }
}
