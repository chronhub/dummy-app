<?php

declare(strict_types=1);

namespace App\Chron\Application\Api;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Projection\Provider\CartProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class UpdateCartItemApi
{
    public function __construct(
        private CartProvider $cartProvider,
        private CartApplicationService $cartApplicationService
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $cart = $this->cartProvider->findRandomOpenedCart();
        $this->ensureCartExists($cart);

        $item = $cart->items->random();
        $itemQuantity = $item->quantity === 1 ? 2 : $item->quantity - 1;

        $this->cartApplicationService->updateCartItemQuantity($item->id, $cart->id, $cart->customer_id, $item->sku_id, $itemQuantity);

        return new JsonResponse([
            'message' => 'Cart item updated successfully',
        ]);
    }

    private function ensureCartExists(?stdClass $cart): void
    {
        if ($cart === null || $cart->items === null || $cart->items->isEmpty()) {
            throw new RuntimeException('No opened cart found or no items in cart');
        }
    }
}
