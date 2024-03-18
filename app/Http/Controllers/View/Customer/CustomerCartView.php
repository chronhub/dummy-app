<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Application\Messaging\Query\QueryFirstTenInventoryItems;
use App\Chron\Application\Messaging\Query\QueryOpenedCartByCustomerId;
use Illuminate\View\View;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerCartView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId): View
    {
        $result = $this->getData($customerId);

        return view('section.customer.cart', [
            'customer' => $result[0],
            'cart' => $result[1],
            'catalog' => $result[2],
        ]);
    }

    private function getData(string $customerId): array
    {
        try {
            return $this->handleQueries([
                Report::relay(new QueryCustomerProfile($customerId)),
                Report::relay(new QueryOpenedCartByCustomerId($customerId)),
                Report::relay(new QueryFirstTenInventoryItems()),
            ]);
        } catch (Throwable $e) {
            report($e);

            abort(501);
        }
    }
}
