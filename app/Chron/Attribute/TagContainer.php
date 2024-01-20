<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\MessageHandlerEntry;
use BadMethodCallException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

use function sprintf;

/**
 * @template T of MessageHandlerEntry
 *
 * @method Collection getBindings()
 * @method Collection getData()
 * @method Collection getEntries()
 * @method array      getQueues()
 */
class TagContainer
{
    use ForwardsCalls;

    public const HANDLER_TAG_PREFIX = '#';

    protected const TAG = 'message.handler.%s';

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

    public function autoTag(): void
    {
        $this->messageMap->load();

        foreach ($this->messageMap->getBindings() as $messageName => $messageHandlers) {
            $this->container->tag($messageHandlers, $this->tagConcrete($messageName));
        }
    }

    /**
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->forwardCallTo($this->messageMap, $method, $parameters);
    }

    protected function tagConcrete(string $concrete, ?int $key = null): string
    {
        $concreteTag = sprintf(self::TAG, Str::remove('\\', Str::snake($concrete)));

        if ($key !== null) {
            return sprintf('%s%s', $concreteTag, self::HANDLER_TAG_PREFIX.$key);
        }

        return $concreteTag;
    }
}
