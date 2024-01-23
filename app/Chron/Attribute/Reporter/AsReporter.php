<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\DomainType;
use Attribute;
use InvalidArgumentException;
use Storm\Contract\Reporter\SubscriberManager;
use Storm\Contract\Tracker\MessageTracker;

#[Attribute(Attribute::TARGET_CLASS)]
class AsReporter
{
    /**
     * Reporter type
     */
    public DomainType $type;

    /**
     * Reporter identifier.
     *
     * Used to bind the reporter into the container
     */
    public string $id;

    /**
     * Whether the reporter is sync or async.
     *
     * Query reporter cannot be async
     *
     * When sync, you can configure your message handlers to be dispatched async.
     * For async, see @defaultQueue
     */
    public bool $sync;

    /**
     * Subscribers to be registered to the reporter.
     *
     * @see SubscriberManager
     */
    public string $subscribers;

    /**
     * Listeners to be registered to the reporter.
     *
     * @var array<string>|array
     */
    public array $listeners;

    /**
     * Default queue to be used when dispatching async.
     *
     * Merged with message handler queues when they are configured as an array.
     * A null default queue means that your handlers:
     *      - will be merged as is in a message job if configured as an array or string
     *      - will change the behavior of your null handler queue to be sync, regardless of the reporter sync property
     */
    public ?string $defaultQueue;

    /**
     * Tracker to be used when fire events.
     *
     * @see MessageTracker as default tracker
     */
    public ?string $tracker;

    public function __construct(
        string $id,
        string|DomainType $type,
        bool $sync,
        string|array $subscribers,
        array $listeners = [],
        ?string $defaultQueue = null,
        ?string $tracker = null,
    ) {
        $this->type = $type instanceof DomainType ? $type : DomainType::from($type);

        if ($this->type === DomainType::QUERY) {
            if ($sync === false) {
                throw new InvalidArgumentException('Query reporter cannot be async');
            }

            if ($defaultQueue !== null) {
                throw new InvalidArgumentException('Query reporter cannot have a default queue');
            }
        }

        $this->id = $id;
        $this->sync = $sync;
        $this->subscribers = $subscribers;
        $this->listeners = $listeners;
        $this->defaultQueue = $defaultQueue;
        $this->tracker = $tracker;
    }
}
