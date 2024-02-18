<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Service;

interface UniqueOwnerPendingOrder
{
    /**
     * Check if a customer has a pending order of status 'created' or 'modified'.
     */
    public function hasPendingOrder(string $ownerOrder): bool;
}
