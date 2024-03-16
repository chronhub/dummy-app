<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\OrderApplicationService;
use Symfony\Component\HttpFoundation\Response;

final class CustomerCancelOrderAction
{
    public function __invoke(OrderApplicationService $orderService, string $customerId, string $orderId): Response
    {
        $orderService->cancelOrderByCustomer($orderId, $customerId);

        return new Response('ok');
    }
}
