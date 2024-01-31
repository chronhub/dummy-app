<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Messaging\MessageMap;
use App\Chron\Attribute\Reporter\DeclaredQueue;
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

final readonly class InMemoryStorage implements KernelStorage
{
    public function __construct(
        protected ReporterMap $reporters,
        protected SubscriberMap $subscribers,
        protected MessageMap $messages,
        protected Application $app
    ) {
    }

    public function findMessage(string $messageName): iterable
    {
        return $this->messages->find($messageName);
    }

    public function getReporterByMessage(array|object $message, ?string $messageClassName = null): string
    {
        if (is_array($message) && $messageClassName === null) {
            throw new InvalidArgumentException('Message class name is required when message is an array');
        }

        $messageClass = is_object($message) ? $message::class : $messageClassName;

        $reporters = $this->findReporterOfMessage($messageClass);

        if ($reporters === []) {
            throw MessageNotFound::withMessageName($messageClass);
        }

        // we do not deal with multiple event reporters
        if (count($reporters) > 1) {
            throw new RuntimeException('Multiple reporters found for message '.$messageClass);
        }

        return $reporters[0];
    }

    public function findReporterOfMessage(string $messageName): array
    {
        return $this->messages
            ->getEntries()
            ->filter(fn (array $messageHandlers, string $message) => $message === $messageName)
            ->values()
            ->collapse()
            ->pluck('reporterId')
            ->unique()
            ->toArray();
    }

    public function getMessages(): Collection
    {
        return $this->messages->getEntries();
    }

    public function getReporters(): Collection
    {
        return $this->reporters->getEntries();
    }

    public function getDeclaredQueues(): DeclaredQueue
    {
        return $this->reporters->getDeclaredQueue();
    }
}
