<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryPaginatedCustomers;
use App\Chron\Package\Reporter\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Storm\Support\QueryPromiseTrait;
use Throwable;

final class CustomerListView
{
    use QueryPromiseTrait;

    public function __invoke(): View
    {
        return view('section.customer.list', [
            'customers' => $this->getCustomers(),
        ]);
    }

    private function getCustomers(): LengthAwarePaginator
    {
        $promise = Report::relay(new QueryPaginatedCustomers());

        try {
            $customers = $this->handlePromise($promise);
        } catch (Throwable $e) {
            report($e);

            abort(404);
        }

        if (! $customers instanceof LengthAwarePaginator) {
            abort(404);
        }

        return $customers;
    }
}
