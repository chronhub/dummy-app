<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Validating;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsValidation
{
    public function __construct(
        /**
         * When validation is too complex to be expressed as an array,
         * or the message instance is not a domain object, you can
         * use a callable validation class, where the message instance is passed.
         *
         * @var string|array $rules
         */
        public string|array $rules,

        /**
         * Whether to validate before or after dispatching the message.
         *
         * When sync, the message will be validated before dispatching.
         * When async, the message will be validated after being dispatched.
         *
         * @var bool
         */
        public bool $beforeDispatch
    ) {
    }
}
