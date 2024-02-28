<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Chron\Application\Service\CustomerService;
use Symfony\Component\HttpFoundation\Response;

final class RegisterRandomCustomerAction
{
    public function __invoke(CustomerService $customerService): Response
    {
        $customerService->registerCustomer();

        return new Response('ok');
    }
}
