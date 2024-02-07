<?php

declare(strict_types=1);

namespace App\Chron\Application\Command\Customer;

use Storm\Message\AbstractDomainCommand;

class ChangeCustomerEmail extends AbstractDomainCommand
{
    public static function withCustomer(string $id, string $newEmail): self
    {
        return new self([
            'id' => $id,
            'new_email' => $newEmail,
        ]);
    }
}