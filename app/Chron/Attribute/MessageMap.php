<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function uksort;

class MessageMap
{
    /**
     * @var Collection<string, array<MessageHandlerData>>
     */
    protected Collection $map;

    protected Collection $bindings;

    public function __construct(
        protected MessageLoader $messageLoader,
        protected ReferenceBuilder $referenceBuilder,
        protected Container $container
    ) {
        $this->map = new Collection();
        $this->bindings = new Collection();
    }

    public function load(): Collection
    {
        $this->messageLoader->getMessages()->each(fn (array $data) => $this->updateMap(...$data));

        return $this->map;
    }

    public function getBindings(): Collection
    {
        return $this->bindings;
    }

    protected function updateMap(
        ReflectionClass $reflectionClass,
        ?ReflectionMethod $reflectionMethod,
        AsCommandHandler|AsEventHandler|AsQueryHandler $attribute
    ): void {
        $handlerMethod = $this->determineHandlerMethod($attribute->method, $reflectionMethod);

        $data = new MessageHandlerData($reflectionClass, $attribute, $handlerMethod);

        if (! $this->map->has($data->handles)) {
            $this->map->put($data->handles, [$data->priority => $data]);
        } else {
            $this->assertCountHandlerPerType($data);

            $messageHandlers = $this->map->get($data->handles);

            if (isset($messageHandlers[$data->priority])) {
                throw new RuntimeException("Duplicate priority $data->priority for $data->handles");
            }

            $messageHandlers[$data->priority] = $data;

            uksort($messageHandlers, fn (int $a, int $b): int => $a <=> $b);

            $this->map->put($data->handles, $messageHandlers);
        }
    }

    protected function determineHandlerMethod(?string $handlerMethod, ?ReflectionMethod $reflectionMethod): string
    {
        return match (true) {
            $handlerMethod !== null => $handlerMethod,
            $reflectionMethod !== null => $reflectionMethod->getName(),
            default => '__invoke',
        };
    }

    private function assertCountHandlerPerType(MessageHandlerData $data): void
    {
        if ($data->type === DomainType::EVENT) {
            return;
        }

        throw new RuntimeException('Only one handler per command and query is allowed');
    }
}
