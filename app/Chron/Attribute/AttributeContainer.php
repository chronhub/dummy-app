<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Storm\Reporter\Exception\MessageNotFound;

use function count;
use function is_array;
use function is_object;

class AttributeContainer
{
    public function __construct(
        protected ReporterContainer $reporterContainer,
        protected MessageContainer $messageContainer,
        protected Application $app
    ) {
    }

    public function autoWire(): void
    {
        $this->reporterContainer->bind();

        $this->messageContainer->tag();
    }

    public function get(string $messageName): iterable
    {
        return $this->messageContainer->find($messageName);
    }

    /**
     * Return reporter name by message name.
     *
     * @throws InvalidArgumentException when the message is an array and message class name is not provided
     * @throws MessageNotFound          when the reporter is not found
     * @throws RuntimeException         when multiple reporters found
     */
    public function getReporterByMessageName(array|object $message, ?string $messageClassName = null): string
    {
        if (is_array($message) && $messageClassName === null) {
            throw new InvalidArgumentException('Message class name is required when message is an array');
        }

        $messageClass = is_object($message) ? $message::class : $messageClassName;

        $reporters = $this->messageContainer->findReporterOfMessage($messageClass);

        if ($reporters === []) {
            throw MessageNotFound::withMessageName($messageClass);
        }

        // we do not deal with multiple event reporters
        if (count($reporters) > 1) {
            throw new RuntimeException('Multiple reporters found for message '.$messageClass);
        }

        return $reporters[0];
    }

    public function getReporterEntries(): Collection
    {
        return $this->reporterContainer->getEntries();
    }

    public function getMessageEntries(): Collection
    {
        return $this->messageContainer->getEntries();
    }

    public function getDeclaredQueues(): array
    {
        return $this->reporterContainer->getQueues();
    }
}
