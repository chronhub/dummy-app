<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Application\Messaging\Command\Cart\CancelCart;
use App\Chron\Application\Messaging\Command\Cart\OpenCart;
use App\Chron\Application\Messaging\Command\Cart\RemoveCartItem;
use App\Chron\Application\Messaging\Command\Cart\UpdateCartItemQuantity;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\InventoryProvider;
use RuntimeException;
use Symfony\Component\Uid\Uuid;

final readonly class CartApplicationService
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function openCart(string $customerId): void
    {
        $command = OpenCart::forCustomer($customerId, Uuid::v4()->jsonSerialize());

        $this->dispatchCommand($command);
    }

    public function addCartItem(string $cartId, string $customerId, string $sku, int $quantity): void
    {
        // todo remove price when aggregate pricing is implemented
        $product = $this->inventoryProvider->findInventoryById($sku);

        if ($product === null) {
            throw new RuntimeException("Product with sku: $sku not found");
        }

        $command = AddCartItem::toCart($cartId, $customerId, $sku, $product->unit_price, $quantity);

        $this->dispatchCommand($command);
    }

    public function removeCartItem(string $cartItemId, string $cartId, string $customerId, string $sku): void
    {
        $command = RemoveCartItem::forCart($cartItemId, $cartId, $customerId, $sku);

        $this->dispatchCommand($command);
    }

    public function updateCartItemQuantity(string $cartItemId, string $cartId, string $customerId, string $sku, int $quantity): void
    {
        // todo remove price when aggregate pricing is implemented
        $product = $this->inventoryProvider->findInventoryById($sku);

        if ($product === null) {
            throw new RuntimeException("Product with sku: $sku not found");
        }

        $command = UpdateCartItemQuantity::toCart(
            $cartItemId,
            $cartId,
            $customerId,
            $sku,
            $product->unit_price,
            $quantity
        );

        $this->dispatchCommand($command);
    }

    public function cancelCart(string $customerId, string $cartId): void
    {
        $command = CancelCart::forCustomer($customerId, $cartId);

        $this->dispatchCommand($command);
    }

    private function dispatchCommand(object $command): void
    {
        Report::relay($command);
    }
}
