<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

use function array_rand;

final class HomeController
{
    public function __invoke(): Response
    {

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
