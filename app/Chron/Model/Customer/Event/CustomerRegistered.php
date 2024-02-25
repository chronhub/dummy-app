<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Event;

use App\Chron\Model\Customer\Birthday;
use App\Chron\Model\Customer\CustomerAddress;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use App\Chron\Model\Customer\Gender;
use App\Chron\Model\Customer\PhoneNumber;
use Storm\Message\AbstractDomainEvent;

final class CustomerRegistered extends AbstractDomainEvent
{
    public static function fromData(
        CustomerId $id,
        CustomerEmail $email,
        CustomerName $name,
        Gender $gender,
        Birthday $birthday,
        PhoneNumber $phoneNumber,
        CustomerAddress $address
    ): self {
        return new self([
            'customer_id' => $id->toString(),
            'customer_email' => $email->value,
            'customer_name' => $name->value,
            'customer_gender' => $gender->value,
            'customer_birthday' => $birthday->value,
            'customer_phone_number' => $phoneNumber->value,
            'customer_address' => $address->toArray(),
        ]);
    }

    public function aggregateId(): CustomerId
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

    public function gender(): Gender
    {
        return Gender::from($this->content['customer_gender']);
    }

    public function birthday(): Birthday
    {
        return Birthday::fromString($this->content['customer_birthday']);
    }

    public function phoneNumber(): PhoneNumber
    {
        return PhoneNumber::fromString($this->content['customer_phone_number']);
    }

    public function address(): CustomerAddress
    {
        return CustomerAddress::fromArray($this->content['customer_address']);
    }
}
