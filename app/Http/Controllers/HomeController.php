<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Model\Customer\CustomerService;
use App\Chron\Model\Order\OrderService;
use Symfony\Component\HttpFoundation\Response;

use function array_rand;

final class HomeController
{
    public function __invoke(CustomerService $customerService, OrderService $orderService): Response
    {
        $rand = [
            fn () => $customerService->registerCustomer(),
            fn () => $customerService->changeCustomerEmail(),
            fn () => $orderService->completeOrder(),
        ];

        $rand[array_rand($rand)]();

        return new Response('ok');
    }
}
