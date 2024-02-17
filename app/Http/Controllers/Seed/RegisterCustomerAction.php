<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seed;

use App\Chron\Application\Service\CustomerService;
use Symfony\Component\HttpFoundation\Response;

final class RegisterCustomerAction
{
    public function __invoke(CustomerService $customerService): Response
    {
        $customerService->registerCustomer();

        return new Response('ok');
    }
}
