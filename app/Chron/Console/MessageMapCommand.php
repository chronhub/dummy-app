<?php

declare(strict_types=1);

namespace App\Chron\Console;

use App\Chron\Attribute\TagContainer;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'reporter-message:map',
    description: 'Map messages',
)]
class MessageMapCommand extends Command
{
    protected $signature = 'reporter-message:map
                            { --message= : Message name }';

    public function __invoke(TagContainer $tagContainer): int
    {
        $map = $tagContainer->map;

        $message = $this->option('message');

        $messages = $this->findInMap($map, $message);

        if ($message && $messages === []) {
            $this->error('Message name not found in map for '.$message);

            return self::FAILURE;
        }

        dump($messages);

        return self::SUCCESS;
    }

    protected function findInMap(array $map, ?string $message): array
    {
        if ($message === null) {
            return $map;
        }

        $found = [];
        foreach ($map as $messageName => $handlers) {
            if ($messageName === $message || class_basename($messageName) === $message) {
                $found += $handlers;
            }
        }

        return $found;
    }
}
