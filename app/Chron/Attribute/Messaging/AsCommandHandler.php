<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Messaging;

use App\Chron\Reporter\DomainType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AsCommandHandler extends AsMessageHandler
{
    public function __construct(
        string $reporter,
        string $handles,
        string|array|null $fromQueue = null,
        ?string $method = null,
    ) {
        parent::__construct(
            $reporter,
            $handles,
            $fromQueue,
            $method,
        );
    }

    public function type(): DomainType
    {
        return DomainType::COMMAND;
    }
}