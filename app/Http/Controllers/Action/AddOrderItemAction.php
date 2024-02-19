<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\OrderService;
use Symfony\Component\HttpFoundation\Response;

final class AddOrderItemAction
{
    public function __invoke(OrderService $orderService, string $customerId, string $orderId): Response
    {
        $orderService->addOrderItem($customerId, $orderId);

        return redirect()->back();
    }
}
