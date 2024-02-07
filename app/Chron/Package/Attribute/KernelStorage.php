<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Package\Attribute\Messaging\MessageAttribute;
use App\Chron\Package\Attribute\Reporter\DeclaredQueue;
use App\Chron\Package\Attribute\Reporter\ReporterAttribute;
use Illuminate\Container\RewindableGenerator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Storm\Reporter\Exception\MessageNotFound;

interface KernelStorage
{
    /**
     * @return iterable<RewindableGenerator|array>
     */
    public function findMessage(string $messageName): iterable;

    /**
     * Find reporter id by message name.
     *
     * @throws InvalidArgumentException when the message is an array and message class name is not provided
     * @throws MessageNotFound          when the reporter is not found
     * @throws RuntimeException         when multiple reporters found
     */
    public function getReporterByMessage(array|object $message, ?string $messageClassName = null): string;

    /**
     * @return Collection<array<string, MessageAttribute>>
     */
    public function getMessages(): Collection;

    /**
     * @return Collection<array<string, ReporterAttribute>>
     */
    public function getReporters(): Collection;

    /**
     * Get the reporters declared queue.
     */
    public function getDeclaredQueues(): DeclaredQueue;
}
