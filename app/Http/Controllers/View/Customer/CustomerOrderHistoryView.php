<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Customer;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Reporter\Report;
use Illuminate\View\View;
use stdClass;
use Storm\Support\QueryPromiseTrait;
use Throwable;

class CustomerOrderHistoryView
{
    use QueryPromiseTrait;

    public function __invoke(string $customerId, string $orderId, OrderList $orderList): View
    {
        return view('customer.order_history', [
            'orderHistory' => $orderList->history($this->getOrderId($orderId)),
            'customer_id' => $customerId,
            'customer' => $this->getCustomerInfo($customerId),
            'order_id' => $orderId,
        ]);
    }

    private function getOrderId(string $orderId): OrderId
    {
        return OrderId::fromString($orderId);
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
