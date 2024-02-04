<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Event;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use Storm\Message\AbstractDomainEvent;

final class CustomerEmailChanged extends AbstractDomainEvent
{
    public static function fromCustomer(CustomerId $customerId, CustomerEmail $newEmail, CustomerEmail $oldEmail): self
    {
        return new self([
            'id' => $customerId->toString(),
            'new_email' => $newEmail->value,
            'old_email' => $oldEmail->value,
        ]);
    }

    public function id(): CustomerId
    {
        return CustomerId::fromString($this->content['id']);
    }

    public function newEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['new_email']);
    }

    public function oldEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['old_email']);
    }
}
