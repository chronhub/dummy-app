<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use App\Chron\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Reporter\Subscribers\HandleCommand;
use App\Chron\Reporter\Subscribers\HandleEvent;
use App\Chron\Reporter\Subscribers\HandleQuery;
use App\Chron\Reporter\Subscribers\MakeMessage;
use App\Chron\Reporter\Subscribers\MessageDecorators;
use App\Chron\Reporter\Subscribers\QueryRouteMessage;
use App\Chron\Reporter\Subscribers\RouteMessage;
use App\Chron\Reporter\Subscribers\TransactionalCommand;
use Illuminate\Support\Collection;

class SubscriberClassMap
{
    protected array $subscribers = [
        MakeMessage::class,
        MessageDecorators::class,
        RouteMessage::class,
        QueryRouteMessage::class,
        HandleCommand::class,
        HandleEvent::class,
        HandleQuery::class,
        TransactionalCommand::class,
        CorrelationHeaderCommand::class,
    ];

    public function getClasses(): Collection
    {
        return collect($this->subscribers);
    }
}
