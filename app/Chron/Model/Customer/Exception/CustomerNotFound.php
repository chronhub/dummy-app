<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Exception;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\DomainException;

class CustomerNotFound extends DomainException
{
    public static function withId(CustomerId $id): self
    {
        return new self("Customer with id {$id->toString()} not found");
    }
}
