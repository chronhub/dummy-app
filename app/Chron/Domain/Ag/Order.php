<?php

declare(strict_types=1);

namespace App\Chron\Domain\Ag;

use App\Chron\Domain\Ag\Concerns\HasAggregateBehaviour;
use Symfony\Component\Uid\Uuid;

class Order
{
    use HasAggregateBehaviour;

    protected string $customerId;

    protected string $status;

    protected array $allStatuses = [];

    public static function create(Uuid $orderId): self
    {
        return new self($orderId);
    }

    protected function apply(DomainEvent $event): void
    {
        $this->customerId = $event->content()->customer_id;
        $this->status = $event->metadata()->event_type;
        $this->allStatuses[] = $event->metadata()->event_type;
        $this->version++;
    }
}
