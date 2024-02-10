<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Model\Customer\CustomerService;
use App\Chron\Model\Order\OrderSagaManagement;
use Symfony\Component\HttpFoundation\Response;

use function array_rand;

final class HomeController
{
    public function __invoke(CustomerService $customerService, OrderSagaManagement $saga): Response
    {
        //$this->batchOrders($saga);

        $rand = [
            fn () => $customerService->registerCustomer(),
            //fn () => $customerService->changeCustomerEmail(),
            fn () => $saga->processLastOrder($customerService->findRandomCustomer()),
        ];

        $rand[array_rand($rand)]();

        return new Response('ok');
    }

    private function batchOrders(OrderSagaManagement $saga): void
    {
        $saga->shipPaidOrders();
        $saga->deliverShippedOrders();
        $saga->returnDeliveredOrders();
        $saga->refundReturnedOrders();
    }
}
