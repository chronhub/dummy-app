<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\OrderService;
use Symfony\Component\HttpFoundation\Response;

final class MakeRandomOrderAction
{
    public function __invoke(OrderService $orderService): Response
    {
        $orderService->makeOrderForRandomCustomer();

        return new Response('ok');
    }
}
