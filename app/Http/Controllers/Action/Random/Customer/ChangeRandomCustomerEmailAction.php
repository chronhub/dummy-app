<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Random\Customer;

use App\Chron\Application\Service\CustomerApplicationService;
use Symfony\Component\HttpFoundation\Response;

final class ChangeRandomCustomerEmailAction
{
    public function __invoke(CustomerApplicationService $customerService): Response
    {
        $customerService->changeCustomerEmail();

        return new Response('ok');
    }
}
