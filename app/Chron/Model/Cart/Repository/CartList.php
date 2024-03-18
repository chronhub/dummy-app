<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Repository;

use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartId;
use Generator;

interface CartList
{
    public function get(CartId $cartId): ?Cart;

    public function save(Cart $cart): void;

    public function history(CartId $cartId): Generator;
}
