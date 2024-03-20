<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Projection\Provider\CustomerProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

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

    private function sum(): array
    {
        $carts = DB::query()
            ->selectRaw('SUM(quantity) as total_quantity')
            ->from('read_cart')
            ->where('status', 'opened')
            ->value('total_quantity');

        $inventory = DB::query()
            ->selectRaw('SUM(reserved) as total_reserved')
            ->from('read_inventory')
            ->value('total_reserved');

        return [
            'carts' => $carts,
            'inventory' => $inventory,
        ];
    }
}
