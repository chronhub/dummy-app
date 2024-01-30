<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\Validating\AsValidation;
use Storm\Contract\Message\DomainCommand;
use Storm\Message\AbstractDomainCommand;

#[AsValidation(
    rules: [
        'customer_id' => 'required|string|uuid',
        'customer_name' => 'required|string|between:3,255|regex:/^[a-zA-Z0-9\s]+$/',
        'customer_email' => 'required|email',
    ],
    beforeDispatch: true,
)]
final class RegisterCustomer extends AbstractDomainCommand
{
    public static function with(string $id, string $name, string $email): DomainCommand
    {
        return new self([
            'customer_id' => $id,
            'customer_name' => $name,
            'customer_email' => $email,
        ]);
    }
}
