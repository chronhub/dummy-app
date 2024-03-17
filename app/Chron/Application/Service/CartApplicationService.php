<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Application\Messaging\Command\Cart\CancelCart;
use App\Chron\Application\Messaging\Command\Cart\CheckoutCart;
use App\Chron\Application\Messaging\Command\Cart\OpenCart;
use App\Chron\Application\Messaging\Command\Cart\QueryAllNonEmptyOpenedCarts;
use App\Chron\Application\Messaging\Command\Cart\QueryInventoryBySku;
use App\Chron\Application\Messaging\Command\Cart\RemoveCartItem;
use App\Chron\Application\Messaging\Command\Cart\UpdateCartItemQuantity;
use App\Chron\Package\Reporter\Report;
use App\Chron\Package\Support\QueryPromiseTrait;
use DomainException;
use Illuminate\Support\LazyCollection;
use stdClass;
use Symfony\Component\Uid\Uuid;

final readonly class CartApplicationService
{
    use QueryPromiseTrait;

    public function openCart(string $customerId): void
    {
        $command = OpenCart::forCustomer($customerId, Uuid::v4()->jsonSerialize());

        $this->dispatchCommand($command);
    }

    public function addProductToCart(string $cartId, string $customerId, string $sku, int $quantity): void
    {
        $product = $this->queryInventoryBySku($sku);

        if ($product === null) {
            throw new DomainException("Product with sku: $sku not found");
        }

        $command = AddCartItem::toCart($cartId, $customerId, $sku, $product->unit_price, $quantity);

        $this->dispatchCommand($command);
    }

    public function removeProductFromCart(string $cartItemId, string $cartId, string $customerId, string $sku): void
    {
        $command = RemoveCartItem::forCart($cartItemId, $cartId, $customerId, $sku);

        $this->dispatchCommand($command);
    }

    public function updateProductQuantity(string $cartItemId, string $cartId, string $customerId, string $sku, int $quantity): void
    {
        $product = $this->queryInventoryBySku($sku);

        if ($product === null) {
            throw new DomainException("Product with sku: $sku not found");
        }

        $command = UpdateCartItemQuantity::toCart(
            $cartItemId,
            $cartId,
            $customerId,
            $sku,
            $product->unit_price,// todo remove
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
        $this->queryAllNonEmptyOpenedCarts()->each(function (stdClass $cart): void {
            $this->dispatchCommand(CheckoutCart::fromCart($cart->id, $cart->customer_id));
        });
    }

    // todo remove query price when aggregate pricing is implemented
    private function queryInventoryBySku(string $sku): ?stdClass
    {
        $query = new QueryInventoryBySku($sku);

        return $this->handlePromise(Report::relay($query));
    }

    private function queryAllNonEmptyOpenedCarts(): LazyCollection
    {
        $query = new QueryAllNonEmptyOpenedCarts();

        return $this->handlePromise(Report::relay($query));
    }

    private function dispatchCommand(object $command): void
    {
        Report::relay($command);
    }
}
