<?php

declare(strict_types=1);

namespace App\Chron\Model\Billing;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case DECLINED = 'declined';
    case CANCELED = 'canceled';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
