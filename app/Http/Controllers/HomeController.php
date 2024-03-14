<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CustomerProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use Illuminate\Contracts\View\View;

final class HomeController
{
    // todo make report queries
    public function __invoke(OrderProvider $orderProvider, InventoryProvider $inventoryProvider, CustomerProvider $customerProvider): View
    {
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
