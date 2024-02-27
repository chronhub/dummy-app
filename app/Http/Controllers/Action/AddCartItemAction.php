<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Projection\Provider\CartProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final readonly class AddCartItemAction
{
    public function __construct(
        private CartProvider $cartProvider,
        private InventoryProvider $inventoryProvider,
        private CartApplicationService $cartApplicationService
    ) {
    }

    public function __invoke(): Response
    {
        $cart = $this->cartProvider->findRandomOpenedCart();

        if ($cart === null) {
            throw new RuntimeException('No opened cart found');
        }

        /** @var Collection $items */
        $items = $cart->items;

        $inventory = $this->inventoryProvider->findRandomItem();

        $randomQuantity = fake()->numberBetween(1, 5);

        if ($items->isEmpty()) {
            $this->cartApplicationService->addCartItem($cart->id, $cart->customer_id, $inventory->id, $randomQuantity);

            return new Response('ok');
        }

        foreach ($items as $item) {
            if ($item->sku_id === $inventory->id) {
                $this->cartApplicationService->updateCartItemQuantity($item->id, $cart->id, $cart->customer_id, $item->sku_id, $randomQuantity);
            }
        }

        return new Response('ok');
    }
}
