<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Order\OrderOwner;

class CustomerNotFound extends DomainException
{
    public static function withId(OrderOwner $id): self
    {
        return new self("Customer with id {$id->toString()} not found");
    }
}
