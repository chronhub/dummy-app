<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Model\Order\OrderPaymentProcess;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: PayOrder::class,
)]
final readonly class PayOrderHandler
{
    public function __construct(private OrderPaymentProcess $orderPaymentProcess)
    {
    }

    public function __invoke(PayOrder $command): void
    {
        $this->orderPaymentProcess->process($command);
    }
}
