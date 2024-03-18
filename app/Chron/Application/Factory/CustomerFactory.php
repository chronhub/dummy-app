<?php

declare(strict_types=1);

namespace App\Chron\Application\Factory;

use App\Chron\Model\Customer\Gender;
use Generator;
use Illuminate\Support\Str;

final class CustomerFactory
{
    public static function makeMany(int $times): Generator
    {
        for ($i = 0; $i < $times; $i++) {
            yield self::make();
        }
    }

    public static function make(): array
    {
        return [
            fake()->uuid,
            self::generateUniqueEmail(),
            fake()->name,
            fake()->randomElement(Gender::toStrings()),
            self::generateBirthday(),
            fake()->phoneNumber,
            [
                'street' => fake()->streetAddress,
                'city' => fake()->city,
                'postal_code' => fake()->postcode,
                'country' => fake()->country,
            ],
        ];
    }

    public static function generateUniqueEmail(): string
    {
        $name = Str::of(fake()->name)->replace(' ', '')->lower();
        $name .= Str::random(4);

        return $name.'@'.fake()->domainName;
    }

    private static function generateBirthday(): string
    {
        $year = fake()->numberBetween(1940, 2006);
        $month = fake()->numberBetween(1, 12);
        $day = fake()->numberBetween(1, 28);

        return $year.'-'.$month.'-'.$day;
    }
}
