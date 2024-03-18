<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use Storm\Message\AbstractDomainCommand;

class ChangeCustomerEmail extends AbstractDomainCommand
{
    public static function withCustomer(string $customerId, string $customerNewEmail): self
    {
        return new self([
            'customer_id' => $customerId,
            'customer_new_email' => $customerNewEmail,
            // current_email
        ]);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function customerNewEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_new_email']);
    }
}
