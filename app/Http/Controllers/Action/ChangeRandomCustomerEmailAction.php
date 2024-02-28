<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\CustomerService;
use Symfony\Component\HttpFoundation\Response;

final class ChangeRandomCustomerEmailAction
{
    public function __invoke(CustomerService $customerService): Response
    {
        $customerService->changeCustomerEmail();

        return new Response('ok');
    }
}
