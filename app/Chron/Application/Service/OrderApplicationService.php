<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Cart\CheckoutCart;
use App\Chron\Package\Reporter\Report;

final readonly class OrderApplicationService
{
    public function checkout(string $customerId, string $cartId): void
    {
        Report::relay(CheckoutCart::fromCart($cartId, $customerId));
    }
}
