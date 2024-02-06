<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Exception;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\DomainException;

class CustomerAlreadyExists extends DomainException
{
    public static function withEmail(CustomerEmail $email): self
    {
        return new self("Customer with email $email->value already exists");
    }

    public static function withId(CustomerId $id): self
    {
        return new self("Customer with id {$id->toString()} already exists");
    }
}
