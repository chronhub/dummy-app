<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Application\Service\OrderApplicationService;
use Illuminate\Http\RedirectResponse;

final readonly class PayCartAction
{
    public function __construct(private OrderApplicationService $orderApplicationService)
    {
    }

    public function __invoke(string $customerId, string $orderId): RedirectResponse
    {
        $this->orderApplicationService->pay($customerId, $orderId);

        return redirect()->back();
    }
}
