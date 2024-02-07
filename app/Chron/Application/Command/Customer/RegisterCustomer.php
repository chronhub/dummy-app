<?php

declare(strict_types=1);

namespace App\Chron\Application\Command\Customer;

use Storm\Message\AbstractDomainCommand;

final class RegisterCustomer extends AbstractDomainCommand
{
    public static function withData(string $id, string $email, string $name): self
    {
        return new self([
            'id' => $id,
            'email' => $email,
            'name' => $name,
        ]);
    }
}
