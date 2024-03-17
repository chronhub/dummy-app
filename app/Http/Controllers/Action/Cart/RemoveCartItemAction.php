<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Application\Service\CartApplicationService;
use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class RemoveCartItemAction
{
    public function __construct(private CartApplicationService $cartApplicationService)
    {
    }

    public function __invoke(string $customerId, string $cartId, string $cartItemId, string $sku): RedirectResponse
    {
        $this->cartApplicationService->removeProductFromCart($cartItemId, $cartId, $customerId, $sku);

        return redirect()->back();
    }
}
