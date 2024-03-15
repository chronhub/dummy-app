<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Random\Customer;

use App\Chron\Model\Customer\Gender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RegisterRandomCustomerAction
{
    public function __invoke(): Response
    {
        $data = [
            fake()->uuid,
            $this->ensureUniqueEmail(),
            fake()->name,
            fake()->randomElement(Gender::toStrings()),
            $this->generateBirthday(),
            fake()->phoneNumber,
            [
                'street' => fake()->streetAddress,
                'city' => fake()->city,
                'postal_code' => fake()->postcode,
                'country' => fake()->country,
            ],
        ];

        $response = Http::acceptJson()->asJson()
            ->post('chronhub.dvl.to/api/customer/register', $data);

        return new Response('ok');
    }

    protected function ensureUniqueEmail(): string
    {
        $name = Str::of(fake()->name)->replace(' ', '')->lower();
        $name .= Str::random(4);

        return $name.'@'.fake()->domainName;
    }

    protected function generateBirthday(): string
    {
        return fake()->date('Y-m-d', '-18 years');
    }
}
