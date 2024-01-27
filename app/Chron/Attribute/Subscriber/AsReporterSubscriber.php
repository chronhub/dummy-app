<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use Attribute;
use Storm\Contract\Reporter\Reporter;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsReporterSubscriber
{
    /**
     * Support reporters.
     *
     * String for one reporter
     * Array for many reporters
     *
     * An array can support wildcard like "reporter.command.*" or "reporter.*" or "*"
     *
     * @var string|array<string>
     */
    public string|array|null $supports;

    /**
     * Event name
     *
     * @see Reporter::DISPATCH_EVENT
     * @see Reporter::FINALIZE_EVENT
     */
    public string $event;

    /**
     * Subscriber priority
     *
     * Must be unique across all subscribers from the same reporter
     */
    public ?int $priority;

    /**
     * Method name
     *
     * Default to "__invoke", optional on method subscriber
     */
    public ?string $method;

    /**
     * Auto wire subscriber to all supported reporters
     */
    public bool $autowire;

    /**
     * Subscriber name
     *
     * To detach subscriber on demand, you can name it,
     * must be unique across all subscribers from the same reporter
     * or, we name it with convention: "fcqn@methodName"
     *
     * Probably use an in memory storage to store subscribers
     * should have access to the reporter tracker
     */
    public ?string $name;

    // once and forget
    // conditional subscribers?

    public function __construct(
        string|array $supports,
        string $event,
        ?string $method = null,
        ?int $priority = null,
        ?string $name = null,
        bool $autowire = false
    ) {
        $this->event = $event;
        $this->supports = $supports;
        $this->method = $method;
        $this->priority = $priority;
        $this->name = $name;
        $this->autowire = $autowire;
    }
}