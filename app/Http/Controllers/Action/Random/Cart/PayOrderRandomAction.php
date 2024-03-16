<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Random\Cart;

use App\Chron\Application\Service\OrderApplicationService;
use App\Chron\Projection\Provider\OrderProvider;
use Symfony\Component\HttpFoundation\Response;

final readonly class PayOrderRandomAction
{
    public function __construct(
        private OrderApplicationService $orderApplicationService,
        private OrderProvider $orderProvider
    ) {
    }

    public function __invoke(): Response
    {
        $order = $this->orderProvider->findRandomPendingOwnerOrder();

        if ($order === null) {
            return new Response('no pending order found');
        }

        $this->orderApplicationService->pay($order->customer_id, $order->id);

        return new Response('ok');
    }
}
