<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Complete;

use App\Chron\Application\Messaging\Query\QueryAllSubmittedCart;
use App\Chron\Application\Messaging\Query\QueryOpenOrderOfCustomer;
use App\Chron\Application\Service\OrderApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\LazyCollection;
use stdClass;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;

final readonly class PayAllSubmittedCartAction
{
    use QueryPromiseTrait;

    public function __construct(private OrderApplicationService $orderApplicationService)
    {
    }

    public function __invoke(): JsonResponse
    {
        $carts = $this->queryAllSubmittedCart();

        foreach ($carts as $cart) {
            $order = $this->queryOpenOrderOfCustomer($cart->customer_id);

            if ($order instanceof stdClass) {
                $this->orderApplicationService->pay($order->customer_id, $order->id);
            }
        }

        return new JsonResponse(['message' => "All {$carts->count()} submitted carts paid"]);
    }

    private function queryAllSubmittedCart(): LazyCollection
    {
        $query = new QueryAllSubmittedCart();

        return $this->handlePromise(Report::relay($query));
    }

    private function queryOpenOrderOfCustomer(string $customerId): ?stdClass
    {
        $query = new QueryOpenOrderOfCustomer($customerId);

        return $this->handlePromise(Report::relay($query));
    }
}
