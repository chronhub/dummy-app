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
            'customer_id' => $customerId->toString(),
            'customer_new_email' => $newEmail->value,
            'customer_old_email' => $oldEmail->value,
        ]);
    }

    public function aggregateId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function newEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_new_email']);
    }

    public function oldEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_old_email']);
    }
}
