<?php

declare(strict_types=1);

namespace App\Chron\Model\Billing;

use Storm\Aggregate\AggregateBehaviorTrait;
use Storm\Contract\Aggregate\AggregateRoot;

final class Billing implements AggregateRoot
{
    use AggregateBehaviorTrait;

    private ClientId $clientId;

    private ClientInfo $clientInfo;

    private string $totalAmount;

    private PaymentStatus $paymentStatus;

    public function createInvoice(BillingId $billingId, ClientId $clientId, ClientInfo $clientInfo, string $totalAmount): self
    {
        $self = new self($billingId);

        $self->recordThat();

        return $self;
    }
}
