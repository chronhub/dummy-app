<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Application\Messaging\Query\QueryOrderOfCustomer;
use Illuminate\View\View;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerOrderView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, string $orderId): View
    {
        $result = $this->getData($customerId, $orderId);

        return view('section.customer.order', [
            'customer' => $result[0],
            'order' => $result[1],
            'customer_id' => $customerId,
        ]);
    }

    private function getData(string $customerId, string $orderId): array
    {
        try {
            return $this->handleQueries([
                Report::relay(new QueryCustomerProfile($customerId)),
                Report::relay(new QueryOrderOfCustomer($customerId, $orderId)),
            ]);
        } catch (Throwable $e) {
            report($e);

            abort(501);
        }
    }
}
