<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use RuntimeException;
use Storm\Aggregate\AggregateBehaviorTrait;
use Storm\Contract\Aggregate\AggregateIdentity;
use Storm\Contract\Aggregate\AggregateRoot;
use Storm\Contract\Message\DomainEvent;

use function sprintf;

final class Customer implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private CustomerEmail $email;

    private CustomerName $name;

    private Gender $gender;

    private Birthday $birthday;

    private PhoneNumber $phoneNumber;

    private CustomerAddress $address;

    public static function register(
        CustomerId $customerId,
        CustomerEmail $email,
        CustomerName $name,
        Gender $gender,
        Birthday $birthday,
        PhoneNumber $phoneNumber,
        CustomerAddress $address
    ): self {
        $self = new self($customerId);

        $self->recordThat(CustomerRegistered::fromData($customerId, $email, $name, $gender, $birthday, $phoneNumber, $address));

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

    public function gender(): Gender
    {
        return $this->gender;
    }

    public function birthday(): Birthday
    {
        return $this->birthday;
    }

    public function phoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
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
                $this->gender = $event->gender();
                $this->birthday = $event->birthday();
                $this->address = $event->address();
                $this->phoneNumber = $event->phoneNumber();

                break;
            case $event instanceof CustomerEmailChanged:
                $this->email = $event->newEmail();

                break;

            default:
                throw new RuntimeException(sprintf('Event %s not supported', $event::class));
        }
    }
}
