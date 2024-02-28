<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CartProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use Illuminate\View\View;
use stdClass;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerCartView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, CartProvider $cartProvider, InventoryProvider $inventoryProvider): View
    {
        return view('section.customer.cart', [
            'customer' => $this->getCustomerInfo($customerId),
            'cart' => $cartProvider->findCartByCustomerId($customerId),
            'catalog' => $inventoryProvider->getFirstTenItems(),
        ]);
    }

    private function getCustomerInfo(string $customerId): stdClass
    {
        $promise = Report::relay(new QueryCustomerProfile($customerId));

        try {
            $customer = $this->handlePromise($promise);
        } catch (Throwable $e) {
            report($e);

            abort(404);
        }

        if (! $customer instanceof stdClass) {
            abort(404);
        }

        return $customer;
    }
}
