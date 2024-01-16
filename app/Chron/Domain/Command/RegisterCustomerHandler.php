<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Infra\CustomerRepository;

final readonly class RegisterCustomerHandler
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    public function __invoke(RegisterCustomer $command): void
    {
        $this->customerRepository->createCustomer(
            $command->content['customer_id'],
            $command->headers(),
            $command->content,
        );
    }
}
