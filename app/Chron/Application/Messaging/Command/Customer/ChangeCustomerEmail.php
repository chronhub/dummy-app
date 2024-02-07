<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use Storm\Message\AbstractDomainCommand;

class ChangeCustomerEmail extends AbstractDomainCommand
{
    public static function withCustomer(string $customerId, string $customerNewEmail): self
    {
        return new self([
            'customer_id' => $customerId,
            'customer_new_email' => $customerNewEmail,
        ]);
    }
}
