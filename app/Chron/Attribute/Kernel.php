<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Messaging\MessageMap;
use App\Chron\Attribute\Reporter\ReporterMap;
use App\Chron\Attribute\Subscriber\SubscriberMap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Storm\Reporter\Exception\MessageNotFound;

use function count;
use function is_array;
use function is_object;

class Kernel
{
    public function __construct(
        protected ReporterMap $reporters,
        protected SubscriberMap $subscribers,
        protected MessageMap $messages,
        protected Application $app
    ) {
    }

    public function bootstraps(): void
    {
        $this->reporters->load();

        $this->subscribers->load(
            $this->reporters->getEntries()->keys()->toArray()
        );

        $this->messages->load($this->reporters->getDeclaredQueues());
    }

    public function get(string $messageName): iterable
    {
        return $this->messages->find($messageName);
    }

    /**
     * Find reporter id by message name.
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

        $reporters = $this->messages->findReporterOfMessage($messageClass);

        if ($reporters === []) {
            throw MessageNotFound::withMessageName($messageClass);
        }

        // we do not deal with multiple event reporters
        if (count($reporters) > 1) {
            throw new RuntimeException('Multiple reporters found for message '.$messageClass);
        }

        return $reporters[0];
    }

    public function reporting(): Collection
    {
        return $this->reporters->getEntries();
    }

    public function messaging(): Collection
    {
        return $this->messages->getEntries();
    }

    /**
     * Get all reporter bindings keys.
     *
     * @return array<string>
     */
    public function getReporterBindings(): array
    {
        return $this->reporters->getEntries()->keys()->toArray();
    }

    /**
     * Get all reporters queues.
     */
    public function getDeclaredQueues(): array
    {
        return $this->reporters->getDeclaredQueues();
    }
}
