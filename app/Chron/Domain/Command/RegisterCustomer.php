<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use Storm\Contract\Message\DomainCommand;
use Storm\Message\AbstractDomainCommand;

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
