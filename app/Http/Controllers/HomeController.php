<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Model\Customer\Command\ChangeCustomerEmail;
use App\Chron\Model\Customer\Command\RegisterCustomer;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Reporter\Report;
use Storm\Stream\StreamName;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;

final class HomeController
{
    use QueryPromiseTrait;

    const CUSTOMER_ID = '11f6c9df-a2e2-3f56-a315-6d886c935a90';

    public function __invoke(CustomerCollection $customers): Response
    {
        dump($customers->get(CustomerId::fromString(self::CUSTOMER_ID)));
        // $this->registerCustomer();
        // $this->changeCustomerEmail();

        return new Response('ok');
    }

    protected function findCustomer(Chronicler $chronicler): void
    {
        $events = $chronicler->retrieveAll(new StreamName('customer'), CustomerId::fromString(self::CUSTOMER_ID));

        dump($events->current());
    }

    protected function registerCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            fake()->email,
            fake()->name
        );

        Report::relay($command);
    }

    protected function changeCustomerEmail(): void
    {
        $command = ChangeCustomerEmail::withCustomer(self::CUSTOMER_ID, fake()->email);

        Report::relay($command);
    }
}
