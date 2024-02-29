<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCartHistory;
use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Package\Reporter\Report;
use App\Chron\Package\Support\QueryPromiseTrait;
use Illuminate\View\View;
use Throwable;

final class CustomerCartHistory
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, string $cartId): View
    {
        $result = $this->getData($customerId, $cartId);

        return view('section.customer.cart_history', [
            'customer' => $result[0],
            'cart_history' => $result[1],
            'cart_id' => $cartId,
        ]);
    }

    private function getData(string $customerId, string $cartId): array
    {
        try {
            return $this->handleQueries([
                Report::relay(new QueryCustomerProfile($customerId)),
                Report::relay(new QueryCartHistory($cartId, $customerId)),
            ]);
        } catch (Throwable $e) {
            report($e);

            abort(501);
        }
    }
}
