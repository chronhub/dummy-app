<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Event;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use Storm\Message\AbstractDomainEvent;

final class CustomerRegistered extends AbstractDomainEvent
{
    public static function fromData(CustomerId $id, CustomerEmail $email, CustomerName $name): self
    {
        return new self([
            'customer_id' => $id->toString(),
            'customer_email' => $email->value,
            'customer_name' => $name->value,
        ]);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function email(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_email']);
    }

    public function name(): CustomerName
    {
        return CustomerName::fromString($this->content['customer_name']);
    }
}