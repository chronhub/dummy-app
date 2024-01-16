<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Infra\OrderRepository;

final readonly class MakeOrderHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function __invoke(MakeOrder $command): void
    {
        $this->orderRepository->createOrder($command->headers());
    }
}
