<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryOrderOfCustomer;
use App\Chron\Package\Reporter\Report;
use Illuminate\View\View;
use stdClass;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerOrderView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, string $orderId): View
    {
        return view('customer_order', [
            'order' => $this->findOrderOfCustomer($customerId, $orderId),
            'customer_id' => $customerId,
        ]);
    }

    private function findOrderOfCustomer(string $customerId, string $orderId): stdClass
    {
        $promise = Report::relay(new QueryOrderOfCustomer($customerId, $orderId));

        try {
            $order = $this->handlePromise($promise);
        } catch (Throwable $e) {
            report($e);

            abort(404);
        }

        if (! $order instanceof stdClass) {
            abort(404);
        }

        return $order;
    }
}
