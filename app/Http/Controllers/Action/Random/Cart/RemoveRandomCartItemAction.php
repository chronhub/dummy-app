<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Random\Cart;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Projection\Provider\CartProvider;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final readonly class RemoveRandomCartItemAction
{
    public function __construct(
        private CartProvider $cartProvider,
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

        if ($items->isEmpty()) {
            throw new RuntimeException('No items found in cart');
        }

        $item = $items->random();

        $this->cartApplicationService->removeCartItem($item->id, $cart->id, $cart->customer_id, $item->sku_id);

        return new Response('ok');
    }
}
