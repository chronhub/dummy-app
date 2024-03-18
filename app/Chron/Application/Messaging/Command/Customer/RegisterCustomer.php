<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Customer;

use App\Chron\Model\Customer\Birthday;
use App\Chron\Model\Customer\CustomerAddress;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use App\Chron\Model\Customer\Gender;
use App\Chron\Model\Customer\PhoneNumber;
use Storm\Message\AbstractDomainCommand;

final class RegisterCustomer extends AbstractDomainCommand
{
    /**
     * @param array{street: string, city: string, state: string, postal_code: string, country: string} $address
     */
    public static function withData(
        string $customerId,
        string $customerEmail,
        string $customerName,
        string $gender,
        string $birthday,
        string $phoneNumber,
        array $address
    ): self {
        return new self([
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'customer_gender' => $gender,
            'customer_birthday' => $birthday,
            'customer_phone_number' => $phoneNumber,
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

    public function customerGender(): Gender
    {
        return Gender::from($this->content['customer_gender']);
    }

    public function customerBirthday(): Birthday
    {
        return Birthday::fromString($this->content['customer_birthday']);
    }

    public function customerPhoneNumber(): PhoneNumber
    {
        return PhoneNumber::fromString($this->content['customer_phone_number']);
    }

    public function customerAddress(): CustomerAddress
    {
        return CustomerAddress::fromArray($this->content['customer_address']);
    }
}
