<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Service;

use Illuminate\Support\Collection;

interface ReadCartItems
{
    /**
     * Find cart items of the cart owner
     */
    public function get(string $cartId, string $ownerId): ?Collection;
}
