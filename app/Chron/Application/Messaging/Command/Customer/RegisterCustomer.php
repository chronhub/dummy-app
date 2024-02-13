<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use Storm\Message\AbstractDomainCommand;

final class RegisterCustomer extends AbstractDomainCommand
{
    /**
     * @param array $address{street: string, city: string, state: string, postal_code: string, country: string}
     */
    public static function withData(string $customerId, string $customerEmail, string $customerName, array $address): self
    {
        return new self([
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'customer_address' => $address,
        ]);
    }
}
