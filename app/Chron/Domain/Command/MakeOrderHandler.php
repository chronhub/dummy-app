<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\Reference;
use App\Chron\Domain\Event\OrderMade;
use App\Chron\Infra\OrderRepository;
use App\Chron\Reporter\ReportEvent;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: MakeOrder::class,
    //fromQueue: ['connection' => 'redis', 'name' => 'default'],
    method: 'command',
)]
final readonly class MakeOrderHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        #[Reference('reporter.event.default')] private ReportEvent $reportEvent,
    ) {
    }

    public function command(MakeOrder $command): void
    {
        $this->orderRepository->createOrder($command->headers());

        $this->reportEvent->relay(OrderMade::fromContent([]));
    }
}
