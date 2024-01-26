<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\MessageHandlerAttribute;
use App\Chron\Attribute\MessageHandler\MessageMap;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function sprintf;

/**
 * @template T of MessageHandlerAttribute
 */
class MessageContainer
{
    public const HANDLER_TAG_PREFIX = '#';

    public const TAG = 'message.handler.%s';

    /**
     * @var array<T>|array
     */
    public array $map = [];

    public function __construct(
        protected MessageMap $messageMap,
        protected Container $container
    ) {
        $this->messageMap->setPrefixResolver(
            fn (string $messageName, ?int $priority) => $this->tagConcrete($messageName, $priority)
        );
    }

    public function find(string $messageName): iterable
    {
        $tagName = $this->tagConcrete($messageName);

        return $this->container->tagged($tagName);
    }

    public function findReporterOfMessage(string $messageName): array
    {
        return $this->messageMap->getEntries()
            ->filter(fn (array $messageHandlers, string $message) => $message === $messageName)
            ->values()
            ->collapse()
            ->pluck('reporterId')
            ->unique()
            ->toArray();
    }

    public function tag(): void
    {
        $this->messageMap->load();

        $this->messageMap->getEntries()
            ->collapse()
            ->groupBy('messageId')
            ->map(fn (Collection $messageHandlers) => $messageHandlers->pluck('handlerId'))
            ->each(fn (Collection $handlerIds, string $messageId) => $this->container->tag($handlerIds->toArray(), $messageId));
    }

    public function getEntries(): Collection
    {
        return $this->messageMap->getEntries();
    }

    protected function tagConcrete(string $concrete, ?int $priority = null): string
    {
        $concreteTag = sprintf(self::TAG, Str::remove('\\', Str::snake($concrete)));

        if ($priority !== null) {
            return sprintf('%s%s', $concreteTag, self::HANDLER_TAG_PREFIX.$priority);
        }

        return $concreteTag;
    }
}
