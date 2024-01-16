<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Infra\CustomerRepository;

final readonly class UpdateCustomerEmailHandler
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    public function __invoke(UpdateCustomerEmail $command): void
    {
        $this->customerRepository->updateRandomCustomerEmail($command->headers());
    }
}
