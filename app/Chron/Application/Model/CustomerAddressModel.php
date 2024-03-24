<?php

declare(strict_types=1);

namespace App\Chron\Application\Model;

use stdClass;

final readonly class CustomerAddressModel
{
    private function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country,
    ) {
    }

    public static function fromObject(stdClass $customer): self
    {
        return new self(
            $customer->street,
            $customer->city,
            $customer->postal_code,
            $customer->country,
        );
    }
}
