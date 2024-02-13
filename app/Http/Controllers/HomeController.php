<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\ReadModel\InventoryReadModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final class HomeController
{
    public function __invoke(InventoryReadModel $readModel): Response
    {
        $availbale = $readModel->getAvailableProductQuantity('98396b68-cee9-41bb-9666-84b6dcd78481');
        dump($availbale);
        //        Report::relay(
        //            CreateProduct::withProduct(
        //                Uuid::v4()->jsonSerialize(),
        //                [
        //                    'name' => 'Product 3',
        //                    'category' => 'Category 3',
        //                    'description' => fake()->sentence,
        //                    'brand' => fake()->company,
        //                    'model' => fake()->word,
        //                ]
        //            ));

        return new Response('ok');

        // dd($customerOrderProvider->findPendingOrders()->count());
        $rand = [
            fn () => $customerService->registerCustomer(),
            //fn () => $customerService->changeCustomerEmail(),
            //fn () => $saga->processOrder($customerService->findRandomCustomer()),
        ];

        $rand[array_rand($rand)]();

        return new Response('ok');
    }
}
