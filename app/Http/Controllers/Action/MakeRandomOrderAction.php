<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\OrderApplicationService;
use Symfony\Component\HttpFoundation\Response;

final class MakeRandomOrderAction
{
    public function __invoke(OrderApplicationService $orderService): Response
    {
        $orderService->makeOrderForRandomCustomer();

        return new Response('ok');
    }
}
