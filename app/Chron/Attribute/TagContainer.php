<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use ReflectionClass;

use function array_merge;
use function sprintf;

class TagContainer
{
    public const TAG = 'message.handler.%s';

    public array $messages = [];

    public array $map = [];

    public function __construct(
        protected SimpleLoader $simpleLoader,
        protected ReferenceBuilder $referenceBuilder,
        protected Container $container
    ) {
    }

    public function find(string $messageName): iterable
    {
        $tagName = $this->nameTag($messageName);

        return $this->container->tagged($tagName);
    }

    public function autoTag(): void
    {
        $this->simpleLoader->register();

        foreach ($this->simpleLoader->messages as $messageName => $messageHandler) {
            $this->processMessageHandlers($messageName, $messageHandler);
        }

        foreach ($this->messages as $messageName => $messageHandlers) {
            $this->container->tag($messageHandlers, $this->nameTag($messageName));
        }
    }

    protected function processMessageHandlers(string $messageName, array $messageHandlers): void
    {
        foreach ($messageHandlers as $key => $handler) {
            [$reflectionClass, $method, $reporterId] = $handler;

            $serviceId = $this->nameTag($messageName, $key);

            $this->container->bind($serviceId, fn (): callable => $this->newInstance($reflectionClass, $method));

            $this->updateMessages($messageName, $serviceId, $key, $reporterId, $reflectionClass, $method);
        }
    }

    protected function updateMessages(
        string $messageName,
        string $serviceId,
        int $key,
        string $reporterId,
        ReflectionClass $reflectionClass,
        string $handlerMethod
    ): void {
        $this->messages[$messageName] = array_merge($this->messages[$messageName] ?? [], [$serviceId]);

        $mapEntry = [
            'reporter_id' => $reporterId,
            'tag_id' => $this->nameTag($serviceId),
            'handler_id' => $serviceId,
            'handler_class' => $reflectionClass->getName(),
            'handler_method' => $handlerMethod,
            'priority' => $key,
        ];

        $this->map[$messageName] = array_merge($this->map[$messageName] ?? [], [$mapEntry]);
    }

    protected function nameTag(string $messageName, ?int $key = null): string
    {
        $tagName = sprintf(self::TAG, Str::remove('\\', Str::snake($messageName)));
        if ($key !== null) {
            $tagName .= '_'.$key;
        }

        return $tagName;
    }

    protected function newInstance(ReflectionClass $messageHandler, ?string $method): callable
    {
        $references = $this->referenceBuilder->fromConstructor($messageHandler);

        $instance = $this->container->make($messageHandler->getName(), ...$references);

        return ($method === null || $method === '__invoke') ? $instance : $instance->{$method}(...);
    }
}
