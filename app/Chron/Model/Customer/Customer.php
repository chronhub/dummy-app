<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use RuntimeException;
use Storm\Contract\Message\DomainEvent;

use function sprintf;

class Customer implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerEmail $email;

    private CustomerName $name;

    private CustomerAddress $address;

    public static function register(CustomerId $customerId, CustomerEmail $email, CustomerName $name, CustomerAddress $address): self
    {
        $self = new self($customerId);

        $self->recordThat(CustomerRegistered::fromData($customerId, $email, $name, $address));

        return $self;
    }

    public function changeEmail(CustomerEmail $email): void
    {
        if ($this->email->sameValueAs($email)) {
            return;
        }

        $this->recordThat(CustomerEmailChanged::fromCustomer($this->customerId(), $email, $this->email));
    }

    public function customerId(): CustomerId
    {
        /** @var AggregateIdentity&CustomerId $identity */
        $identity = $this->identity;

        return $identity;
    }

    public function email(): CustomerEmail
    {
        return $this->email;
    }

    public function name(): CustomerName
    {
        return $this->name;
    }

    public function address(): CustomerAddress
    {
        return $this->address;
    }

    protected function apply(DomainEvent $event): void
    {
        switch (true) {
            case $event instanceof CustomerRegistered:
                $this->email = $event->email();
                $this->name = $event->name();
                $this->address = $event->address();

                break;
            case $event instanceof CustomerEmailChanged:
                $this->email = $event->newEmail();

                break;

            default:
                throw new RuntimeException(sprintf('Event %s not supported', $event::class));
        }
    }
}
