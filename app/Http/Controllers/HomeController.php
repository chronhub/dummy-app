<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CustomerProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use Illuminate\Contracts\View\View;
use Symfony\Component\Uid\Uuid;

final class HomeController
{
    // todo make report queries
    public function __invoke(OrderProvider $orderProvider, InventoryProvider $inventoryProvider, CustomerProvider $customerProvider): View
    {
        //        Report::relay(AddOrderItem::forOrder(
        //            orderId: '2ac93e7a-4d36-41c2-841e-43236c309903',
        //            orderItemId: Uuid::v4()->jsonSerialize(),
        //            skuId: '4174e82b-4103-41f6-9d94-af0e14e17cb2',
        //            customerId: '10dc19cc-18cd-337d-a526-7094ba687c8a',
        //            unitPrice: '278.31',
        //            quantity: 200
        //        ));
        //
        //        return 'ok';
        $order = $orderProvider->getOrderSummary();
        $inventory = $inventoryProvider->getInventorySummary();
        $lastTenCustomers = $customerProvider->lastTenCustomers();

        return view('section.overview.index', [
            'order' => $order,
            'inventory' => $inventory,
            'lastTenCustomers' => $lastTenCustomers,
        ]);
    }
}
