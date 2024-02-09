<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Package\Attribute\Chronicler\ChroniclerMap;
use App\Chron\Package\Attribute\Messaging\MessageMap;
use App\Chron\Package\Attribute\Reporter\ReporterMap;
use App\Chron\Package\Attribute\StreamSubscriber\StreamSubscriberMap;
use App\Chron\Package\Attribute\Subscriber\SubscriberMap;
use Illuminate\Contracts\Foundation\Application;

class Kernel
{
    public static bool $loaded = false;

    public function __construct(
        protected ReporterMap $reporters,
        protected SubscriberMap $subscribers,
        protected MessageMap $messages,
        protected ChroniclerMap $chroniclers,
        protected StreamSubscriberMap $streamSubscribers,
        protected Application $app
    ) {
    }

    public function boot(): void
    {
        if (self::$loaded === true) {
            return;
        }

        $this->chroniclers->load();

        $this->streamSubscribers->load(
            $this->chroniclers->getEntries()->keys()->toArray()
        );

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
            $this->chroniclers,
            $this->streamSubscribers,
            $this->app
        );
    }
}
