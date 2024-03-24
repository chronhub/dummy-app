<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\CheckoutCart;
use App\Chron\Model\Order\OrderCreationProcess;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.async.default',
    handles: CheckoutCart::class,
)]
final readonly class CheckoutCartHandler
{
    public function __construct(private OrderCreationProcess $orderDomainService)
    {
    }

    public function __invoke(CheckoutCart $command): void
    {
        $this->orderDomainService->newOrder($command->cartId(), $command->cartOwner());
    }
}
