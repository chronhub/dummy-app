<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\MessageHandlerAttribute;
use App\Chron\Attribute\MessageHandler\MessageHandlerMap;
use BadMethodCallException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

use function sprintf;

/**
 * @template T of MessageHandlerAttribute
 *
 * @method Collection getBindings()
 * @method Collection getEntries()
 */
class TagHandlerContainer
{
    use ForwardsCalls;

    public const HANDLER_TAG_PREFIX = '#';

    public const TAG = 'message.handler.%s';

    /**
     * @var array<T>|array
     */
    public array $map = [];

    public function __construct(
        protected MessageHandlerMap $messageMap,
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

    public function tag(): void
    {
        $this->messageMap->load();

        $this->messageMap->getBindings()->each(
            fn (array $messageHandlers, string $messageName) => $this->container->tag(
                $messageHandlers,
                $this->tagConcrete($messageName)
            )
        );
    }

    /**
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->forwardCallTo($this->messageMap, $method, $parameters);
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
