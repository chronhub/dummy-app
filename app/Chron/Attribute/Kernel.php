<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Messaging\MessageMap;
use App\Chron\Attribute\Reporter\ReporterMap;
use App\Chron\Attribute\Subscriber\SubscriberMap;
use Illuminate\Contracts\Foundation\Application;

class Kernel
{
    public static bool $loaded = false;

    public function __construct(
        protected ReporterMap $reporters,
        protected SubscriberMap $subscribers,
        protected MessageMap $messages,
        protected Application $app
    ) {
    }

    public function boot(): void
    {
        if (self::$loaded === true) {
            return;
        }

        $this->reporters->load();

        $this->subscribers->load(
            $this->reporters->getEntries()->keys()->toArray()
        );

        $this->messages->load($this->reporters->getDeclaredQueue());

        self::$loaded = true;
    }

    public function getStorage(): InMemoryStorage
    {
        return new InMemoryStorage(
            $this->reporters,
            $this->subscribers,
            $this->messages,
            $this->app
        );
    }
}
