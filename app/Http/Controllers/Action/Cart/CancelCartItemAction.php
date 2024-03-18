<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Application\Service\CartApplicationService;
use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class CancelCartItemAction
{
    public function __construct(private CartApplicationService $cartApplicationService)
    {
    }

    public function __invoke(string $customerId, string $cartId): RedirectResponse
    {
        $this->cartApplicationService->cancelCart($customerId, $cartId);

        return redirect()->back();
    }
}
