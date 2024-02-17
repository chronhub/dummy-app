<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use Illuminate\View\View;

final class HomeController
{
    // todo make report queries
    public function __invoke(OrderProvider $orderProvider, InventoryProvider $inventoryProvider): View
    {
        $order = $orderProvider->getOrderSummary();
        $inventory = $inventoryProvider->getInventorySummary();

        return view('home', [
            'order' => $order,
            'inventory' => $inventory,
        ]);
    }
}
