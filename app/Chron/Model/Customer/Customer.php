<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Aggregate\AggregateBehaviorTrait;
use App\Chron\Aggregate\Contract\AggregateRoot;
use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Model\Customer\Event\CustomerRegistered;

class Customer implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerEmail $email;

    private CustomerName $name;

    public static function register(CustomerId $customerId, CustomerEmail $email, CustomerName $name): self
    {
        $self = new self($customerId);

        $self->recordThat(CustomerRegistered::fromData($customerId, $email, $name));

        return $self;
    }

    public function changeEmail(CustomerEmail $email): void
    {
        if ($this->email->equalsTo($email)) {
            return;
        }

        $this->recordThat(CustomerEmailChanged::fromCustomer($this->customerId(), $email, $this->email));
    }

    public function customerId(): CustomerId
    {
        return $this->identity;
    }

    public function email(): CustomerEmail
    {
        return $this->email;
    }

    public function name(): CustomerName
    {
        return $this->name;
    }

    protected function applyCustomerRegistered(CustomerRegistered $event): void
    {
        $this->email = $event->email();
        $this->name = $event->name();
    }

    protected function applyCustomerEmailChanged(CustomerEmailChanged $event): void
    {
        $this->email = $event->newEmail();
    }
}
