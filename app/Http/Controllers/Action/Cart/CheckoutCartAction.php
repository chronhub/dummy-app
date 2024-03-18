<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Application\Service\CartApplicationService;
use Illuminate\Http\RedirectResponse;

final readonly class CheckoutCartAction
{
    public function __construct(private CartApplicationService $cart)
    {
    }

    public function __invoke(string $customerId, string $cartId): RedirectResponse
    {
        $this->cart->checkout($customerId, $cartId);

        return redirect()->back();
    }
}
