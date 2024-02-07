<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use Storm\Message\AbstractDomainCommand;

final class RegisterCustomer extends AbstractDomainCommand
{
    public static function withData(string $customerId, string $customerEmail, string $customerName): self
    {
        return new self([
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
        ]);
    }
}
