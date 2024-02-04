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
            'id' => $id->toString(),
            'email' => $email->value,
            'name' => $name->value,
        ]);
    }

    public function id(): CustomerId
    {
        return CustomerId::fromString($this->content['id']);
    }

    public function email(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['email']);
    }

    public function name(): CustomerName
    {
        return CustomerName::fromString($this->content['name']);
    }
}
