<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

// todo validate address

final readonly class CustomerAddress
{
    private function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country
    ) {
    }

    /**
     * @param array{street: string, city: string, postal_code: string, country: string} $address
     */
    public static function fromArray(array $address): self
    {
        return new self(
            $address['street'],
            $address['city'],
            $address['postal_code'],
            $address['country']
        );
    }

    /**
     * @return array{street: string, city: string, postal_code: string, country: string}
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
        ];
    }

    public function sameValueAs(self $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->postalCode === $other->postalCode
            && $this->country === $other->country;
    }
}
