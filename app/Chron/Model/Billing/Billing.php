<?php

declare(strict_types=1);

namespace App\Chron\Model\Billing;

use App\Chron\Package\Aggregate\AggregateBehaviorTrait;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

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
