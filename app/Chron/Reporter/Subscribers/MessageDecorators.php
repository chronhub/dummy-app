<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Reference\Reference;
use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Closure;
use Storm\Contract\Message\MessageDecorator;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;

#[AsReporterSubscriber(
    supports: ['*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 98000,
    autowire: true,
)]
final readonly class MessageDecorators
{
    public function __construct(#[Reference('message.decorator.chain.default')] private MessageDecorator $messageDecorator)
    {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $this->messageDecorator->decorate($story->message());

            $story->withMessage($message);
        };
    }
}
