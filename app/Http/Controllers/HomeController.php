<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Application\Service\CustomerService;
use App\Chron\Application\Service\OrderService;
use Symfony\Component\HttpFoundation\Response;

use function array_rand;

final class HomeController
{
    public function __invoke(OrderService $orderService, CustomerService $customerService): Response
    {
        //$customerService->registerCustomer();
        $orderService->makeOrderForRandomCustomer();

        return new Response('ok');

        // dd($customerOrderProvider->findPendingOrders()->count());
        $rand = [
            //fn () => $customerService->registerCustomer(),
            fn () => $customerService->changeCustomerEmail(),
            //fn () => $saga->processOrder($customerService->findRandomCustomer()),
        ];

        $rand[array_rand($rand)]();

        return new Response('ok');
    }
}
