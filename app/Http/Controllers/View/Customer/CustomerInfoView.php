<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Application\Messaging\Query\QueryOrdersSummaryOfCustomer;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CartProvider;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use stdClass;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerInfoView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, CartProvider $cartProvider): View
    {
        return view('section.customer.index', [
            'customer' => $this->getCustomerInfo($customerId),
            'orders' => $this->getOrdersSummary($customerId),
            'cart' => $cartProvider->findCartByCustomerId($customerId),
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

    private function getOrdersSummary(string $customerId): Collection
    {
        $promise = Report::relay(new QueryOrdersSummaryOfCustomer($customerId));

        try {
            $orders = $this->handlePromise($promise);
        } catch (Throwable $e) {
            report($e);

            abort(404);
        }

        if (! $orders instanceof Collection) {
            abort(404);
        }

        return $orders;
    }
}
