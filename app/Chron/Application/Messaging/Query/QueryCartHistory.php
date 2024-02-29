<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Customer\CustomerId;

final readonly class QueryCartHistory
{
    public function __construct(
        private string $cartId,
        private string $customerId,
        private int $from = 1,
        private ?int $to = null
    ) {
    }

    public function cartId(): CartId
    {
        return CartId::fromString($this->cartId);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->customerId);
    }

    public function from(): int
    {
        return $this->from;
    }

    public function to(): ?int
    {
        return $this->to;
    }
}
