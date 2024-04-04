<?php

declare(strict_types=1);

namespace App\Chron\Application\Api;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Application\Service\ShopApplicationService;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class AddCartItemApi
{
    public function __construct(
        private CartApplicationService $cartApplicationService,
        private ShopApplicationService $shopApplicationService
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $cart = $this->shopApplicationService->queryRandomOpenCart();
            $product = $this->shopApplicationService->queryRandomAvailableProductFromCatalog();

            if ($cart->items->isEmpty()) {
                return $this->addProductToCart($cart, $product);
            }

            return new JsonResponse(['message' => 'todo update'], 500);

            //return $this->updateProductQuantity($cart, $product);
        } catch (Throwable $exception) {
            report($exception);

            return new JsonResponse(['message' => $exception->getMessage()], 500);
        }
    }

    private function addProductToCart(stdClass $cart, stdClass $product): JsonResponse
    {
        $this->cartApplicationService->addProductToCart(
            $cart->id,
            $cart->customer_id,
            $product->id,
            $this->generateRandomQuantity()
        );

        return new JsonResponse(['message' => 'Cart item added successfully']);
    }

    private function updateProductQuantity(stdClass $cart, stdClass $product): JsonResponse
    {
        $cartItem = $this->findProductInCart($cart, $product);

        if ($cartItem instanceof stdClass) {
            $this->cartApplicationService->updateCartProductQuantity(
                $cartItem->id,
                $cart->id,
                $cart->customer_id,
                $product->id,
                $this->generateRandomQuantity()
            );

            return new JsonResponse(['message' => 'Cart item updated successfully']);
        }

        return new JsonResponse(['message' => 'Cart item not found'], Response::HTTP_NOT_MODIFIED);
    }

    private function findProductInCart(stdClass $cart, stdClass $product): ?stdClass
    {
        foreach ($cart->items as $item) {
            if ($item->sku_id === $product->id) {
                return $item;
            }
        }

        return null;
    }

    private function generateRandomQuantity(): int
    {
        return fake()->numberBetween(1, 5);
    }
}
