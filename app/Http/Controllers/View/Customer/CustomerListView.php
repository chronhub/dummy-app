<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryPaginatedCustomers;
use App\Chron\Package\Reporter\Report;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerListView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, int $page = 0): View
    {
        return view('customer_list', [
            'customers' => $this->getCustomers($page),
        ]);
    }

    private function getCustomers(int $page): Collection
    {
        $promise = Report::relay(new QueryPaginatedCustomers($page, 10));

        try {
            $customers = $this->handlePromise($promise);
        } catch (Throwable $e) {
            report($e);

            abort(404);
        }

        if (! $customers instanceof Collection) {
            abort(404);
        }

        return $customers;
    }
}
