<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use stdClass;

final readonly class CustomerModel
{
    private function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $phone,
        public CustomerAddressModel $address,
        public string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromObject(stdClass $customer): self
    {
        return new CustomerModel(
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->phone_number,
            CustomerAddressModel::fromObject($customer),
            $customer->created_at,
            $customer->updated_at
        );
    }
}
