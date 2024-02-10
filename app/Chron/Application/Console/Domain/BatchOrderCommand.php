<?php

declare(strict_types=1);

namespace App\Chron\Application\Console\Domain;

use App\Chron\Model\Order\OrderSagaManagement;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\SignalableCommandInterface;

use function pcntl_async_signals;
use function sleep;
use function sprintf;

#[AsCommand(
    name: 'order:batch',
    description: 'Process batch order'
)]
class BatchOrderCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'order:batch';

    protected bool $shouldQuit = false;

    public function __invoke(OrderSagaManagement $orderSagaManagement): int
    {
        pcntl_async_signals(true);

        $this->info('Processing batch orders...');

        while (! $this->shouldQuit) {
            foreach ($this->batchOperation($orderSagaManagement) as $operation => $result) {
                $this->components->info(sprintf('%s %s orders', $operation, $result));

                sleep(5);
            }
        }

        return self::SUCCESS;
    }

    protected function batchOperation(OrderSagaManagement $saga): array
    {
        return [
            'ship' => $saga->shipPaidOrders(),
            'deliver' => $saga->deliverShippedOrders(),
            'return' => $saga->returnDeliveredOrders(),
            'refund' => $saga->refundReturnedOrders(),
            'close' => $saga->closeCancelledOrRefundedOrders(),
            'close_overdue' => $saga->closeOverdueDeliveredOrder(),
        ];
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal)
    {
        $this->shouldQuit = true;

        $this->info('Quitting...');

        return self::SUCCESS;
    }
}
