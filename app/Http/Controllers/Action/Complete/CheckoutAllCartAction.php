<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Complete;

use App\Chron\Application\Service\CartApplicationService;
use Illuminate\Http\JsonResponse;

final readonly class CheckoutAllCartAction
{
    public function __construct(private CartApplicationService $cart)
    {
    }

    public function __invoke(): JsonResponse
    {
        $this->cart->checkoutAll();

        return new JsonResponse(['message' => 'All carts checked out']);
    }
}
