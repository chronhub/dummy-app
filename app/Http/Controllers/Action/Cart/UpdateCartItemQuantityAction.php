<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Application\Service\CartApplicationService;
use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class UpdateCartItemQuantityAction
{
    public function __construct(private CartApplicationService $cartApplicationService)
    {
    }

    public function __invoke(string $customerId, string $cartId, string $cartItemId, string $sku, string $quantity): RedirectResponse
    {
        $this->cartApplicationService->updateCartProductQuantity($cartItemId, $cartId, $customerId, $sku, (int) $quantity);

        return redirect()->back();
    }
}
