<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute;

use App\Chron\Package\Attribute\Messaging\MessageLoader;
use App\Chron\Package\Attribute\Reporter\ReporterLoader;
use App\Chron\Package\Attribute\Subscriber\SubscriberLoader;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class KernelLoader
{
    protected ?LazyCollection $loaders = null;

    public function __construct(
        protected ReporterLoader $reporterLoader,
        protected MessageLoader $messageLoader,
        protected SubscriberLoader $subscriberLoader
    ) {

    }

    /**
     * Load all the attributes from the loaders
     *
     * @return LazyCollection<array{'reporters' : string, Collection, 'messages': string, Collection, 'subscribers': string, Collection}>
     */
    public function load(): LazyCollection
    {
        $this->loaders = LazyCollection::make([
            'reporters' => $this->reporterLoader->getAttributes(),
            'messages' => $this->messageLoader->getAttributes(),
            'subscribers' => $this->subscriberLoader->getAttributes(),
        ]);

        return $this->loaders;
    }
}
