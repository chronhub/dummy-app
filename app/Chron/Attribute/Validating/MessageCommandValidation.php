<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Validating;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;

use function is_array;

#[AsReporterSubscriber(
    supports: ['reporter.command.*'],
    event: Reporter::DISPATCH_EVENT,
    priority: 50000,
    autowire: false,
)]
final class MessageCommandValidation
{
    /**
     * @var array|callable
     */
    private $rules;

    public function __construct(
        callable|array $rules,
        private readonly bool $beforeDispatch,
    ) {
        $this->rules = $rules;
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            $hasBeenDispatched = $this->hasBeenDispatched($message);

            if ($this->shouldValidate($hasBeenDispatched)) {
                $this->validate($message);
            }
        };
    }

    /**
     * Validate the message.
     *
     * @throws ValidationException
     */
    private function validate(Message $message): void
    {
        if (is_array($this->rules) && $message->isMessaging()) {
            $validator = Validator::make([
                'headers' => $message->headers(),
                'content' => $message->event()->toContent(),
            ], $this->rules);

            $validator->validate();
        } else {
            ($this->rules)($message);
        }
    }

    private function shouldValidate(bool $hasBeenDispatched): bool
    {
        return $this->beforeDispatch && ! $hasBeenDispatched
            || ! $this->beforeDispatch && $hasBeenDispatched;
    }

    private function hasBeenDispatched(Message $message): bool
    {
        return $message->header(Header::EVENT_DISPATCHED) === true;
    }
}
