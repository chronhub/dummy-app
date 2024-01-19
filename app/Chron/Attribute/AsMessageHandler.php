<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Attribute;
use InvalidArgumentException;

/**
 * Attribute class for marking a class or method as a message handler.
 *
 * This attribute is intended to be used with the #[AsMessageHandler] attribute declaration.
 * It allows specifying various properties for configuring the message handler behavior.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsMessageHandler
{
    /**
     * Reporter identifier for the message handler.
     *
     * @required For documentation purposes.
     */
    public string $reporter;

    /**
     * Message name that the handler handles.
     *
     * @required  Only one message per handler. This simplifies the implementation and avoids certain complexities.
     */
    public string $handles;

    /**
     * The name of the queue from which the handler should listen for messages.
     * Only event handler can define multiple queues.
     */
    public string|array|null $fromQueue = null;

    /**
     * The method to be invoked on the handler class.
     * Defaults to "__invoke" and optional in class methods.
     */
    public ?string $method = null;

    /**
     * Priority of the message handler when multiple handlers exist within the same class or other classes.
     *
     * Must be unique when using multiple handlers within the same class and/or in other classes.
     */
    public int $priority = 0;

    /**
     * Constructor for the AsMessageHandler attribute.
     *
     * @param string            $reporter  Reporter identifier for the message handler.
     * @param string            $handles   Message name that the handler handles.
     * @param string|array|null $fromQueue The name of the queue from which the handler should listen for messages.
     * @param string|null       $method    The method to be invoked on the handler class. Defaults to "__invoke".
     * @param int               $priority  Priority of the message handler when multiple handlers exist.
     *
     * @throws InvalidArgumentException If the $reporter property is blank.
     * @throws InvalidArgumentException If the $handles property is blank.
     * @throws InvalidArgumentException If the $priority property is less than zero.
     */
    public function __construct(
        string $reporter,
        string $handles,
        // todo add DomainType
        string|array|null $fromQueue = null,
        ?string $method = null,
        int $priority = 0
    ) {
        if (blank($reporter)) {
            throw new InvalidArgumentException('Reporter id cannot be blank');
        }

        if (blank($handles)) {
            throw new InvalidArgumentException('Handles cannot be blank');
        }

        if ($priority < 0) {
            throw new InvalidArgumentException('Priority cannot be less than zero');
        }

        $this->reporter = $reporter;
        $this->handles = $handles;
        $this->fromQueue = $fromQueue;
        $this->method = $method;
        $this->priority = $priority;
    }
}
