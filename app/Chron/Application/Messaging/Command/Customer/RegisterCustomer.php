<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use App\Chron\Model\Customer\CustomerAddress;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use Storm\Message\AbstractDomainCommand;

final class RegisterCustomer extends AbstractDomainCommand
{
    /**
     * @param array{street: string, city: string, state: string, postal_code: string, country: string} $address
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

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function customerEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_email']);
    }

    public function customerName(): CustomerName
    {
        return CustomerName::fromString($this->content['customer_name']);
    }

    public function customerAddress(): CustomerAddress
    {
        return CustomerAddress::fromArray($this->content['customer_address']);
    }
}
