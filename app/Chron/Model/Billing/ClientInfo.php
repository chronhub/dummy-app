<?php

declare(strict_types=1);

namespace App\Chron\Model\Billing;

use App\Chron\Model\Customer\CustomerAddress;

final readonly class ClientInfo
{
    private function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country
    ) {
    }

    public static function fromArray(array $address): self
    {
        return new self(
            $address['street'],
            $address['city'],
            $address['postal_code'],
            $address['country']
        );
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
        ];
    }

    public function sameValueAs(CustomerAddress $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->postalCode === $other->postalCode
            && $this->country === $other->country;
    }
}
