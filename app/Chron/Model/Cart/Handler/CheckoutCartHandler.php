<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Handler;

use App\Chron\Application\Messaging\Command\Cart\CheckoutCart;
use App\Chron\Model\Order\OrderDomainService;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CheckoutCart::class,
)]
final readonly class CheckoutCartHandler
{
    public function __construct(private OrderDomainService $orderDomainService)
    {
    }

    public function __invoke(CheckoutCart $command): void
    {
        $this->orderDomainService->createOrder($command->cartId(), $command->cartOwner());
    }
}
