<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Cart\Service\ReadCartItems;
use App\Chron\Projection\Provider\CartProvider;
use Illuminate\Support\Collection;

final readonly class CartItemsFromRead implements ReadCartItems
{
    public function __construct(private CartProvider $cartProvider)
    {
    }

    public function get(string $cartId, string $ownerId): ?Collection
    {
        $cart = $this->cartProvider->findCartWithOwner($cartId, $ownerId);

        return $cart?->items;
    }
}
