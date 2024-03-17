<?php

declare(strict_types=1);

namespace App\Chron\Application\Api;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Projection\Provider\CartProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class AddCartItemApi
{
    public function __construct(
        private CartProvider $cartProvider,
        private InventoryProvider $inventoryProvider,
        private CartApplicationService $cartApplicationService
    ) {
    }

    public function __invoke(): Response
    {
        $cart = $this->getCart();

        /** @var Collection $items */
        $items = $cart->items;

        $inventory = $this->inventoryProvider->findRandomItem();
        $randomQuantity = fake()->numberBetween(1, 5);

        if ($items->isEmpty()) {
            $this->cartApplicationService->addProductToCart($cart->id, $cart->customer_id, $inventory->id, $randomQuantity);

            return new JsonResponse([
                'message' => 'Cart item added successfully',
            ]);
        }

        foreach ($items as $item) {
            if ($item->sku_id === $inventory->id) {
                $this->cartApplicationService->updateProductQuantity($item->id, $cart->id, $cart->customer_id, $item->sku_id, $randomQuantity);
            }
        }

        return new JsonResponse([
            'message' => 'Cart item updated successfully',
        ]);
    }

    private function getCart(): stdClass
    {
        $cart = $this->cartProvider->findRandomOpenedCart();

        if ($cart === null) {
            throw new RuntimeException('No opened cart found');
        }

        return $cart;
    }
}
