<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Package\Attribute\Messaging\MessageMap;
use App\Chron\Package\Attribute\Reporter\ReporterMap;
use App\Chron\Package\Attribute\Subscriber\SubscriberMap;
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

        $this->messages->load($this->reporters->getDeclaredQueue());

        $this->subscribers->load(
            $this->reporters->getEntries()->keys()->toArray()
        );

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
