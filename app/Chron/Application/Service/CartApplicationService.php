<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Application\Messaging\Command\Cart\CancelCart;
use App\Chron\Application\Messaging\Command\Cart\CheckoutCart;
use App\Chron\Application\Messaging\Command\Cart\OpenCart;
use App\Chron\Application\Messaging\Command\Cart\RemoveCartItem;
use App\Chron\Application\Messaging\Command\Cart\UpdateCartItemQuantity;
use stdClass;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\Uid\Uuid;

final readonly class CartApplicationService
{
    use QueryPromiseTrait;

    public function __construct(private ShopApplicationService $shopApplicationService)
    {
    }

    public function openCart(string $customerId): void
    {
        $command = OpenCart::forCustomer($customerId, Uuid::v4()->jsonSerialize());

        $this->dispatchCommand($command);
    }

    public function addProductToCart(string $cartId, string $customerId, string $sku, int $quantity): void
    {
        $product = $this->shopApplicationService->queryProductFromCatalog($sku);

        $command = AddCartItem::toCart($cartId, $customerId, $sku, $product->current_price, $quantity);

        $this->dispatchCommand($command);
    }

    public function removeProductFromCart(string $cartItemId, string $cartId, string $customerId, string $sku): void
    {
        $command = RemoveCartItem::forCart($cartItemId, $cartId, $customerId, $sku);

        $this->dispatchCommand($command);
    }

    public function updateCartProductQuantity(string $cartItemId, string $cartId, string $customerId, string $sku, int $quantity): void
    {
        $product = $this->shopApplicationService->queryProductFromCatalog($sku);

        $command = UpdateCartItemQuantity::toCart(
            $cartItemId,
            $cartId,
            $customerId,
            $sku,
            $product->current_price,// todo remove
            $quantity
        );

        $this->dispatchCommand($command);
    }

    public function cancelCart(string $customerId, string $cartId): void
    {
        $command = CancelCart::forCustomer($customerId, $cartId);

        $this->dispatchCommand($command);
    }

    public function checkout(string $customerId, string $cartId): void
    {
        $this->dispatchCommand(CheckoutCart::fromCart($cartId, $customerId));
    }

    public function checkoutAll(): void
    {
        $carts = $this->shopApplicationService->queryAllNonEmptyOpenedCarts();

        $carts->each(function (stdClass $cart): void {
            $this->dispatchCommand(CheckoutCart::fromCart($cart->id, $cart->customer_id));
        });
    }

    private function dispatchCommand(object $command): void
    {
        Report::relay($command);
    }
}
