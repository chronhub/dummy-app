<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Domain\Command\MakeOrder;
use App\Chron\Domain\Command\RegisterCustomer;
use App\Chron\Domain\Command\UpdateCustomerEmail;
use App\Chron\Reporter\Report;
use Storm\Contract\Message\Header;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final class HomeController
{
    use QueryPromiseTrait;

    public function __invoke(): Response
    {
        $command = $this->getRandomCommand();

        Report::relay($command);

        return new Response('ok');
    }

    private function getRandomCommand(): object
    {
        $works = [
            fn () => $this->registerCustomer(),
            // fn () => $this->updateEmailCustomer(),
            // fn () => $this->makeOrder(),
        ];

        return $works[array_rand($works)]();
    }

    private function registerCustomer(): object
    {
        return RegisterCustomer::with(Uuid::v4()->jsonSerialize(), fake()->name, fake()->email)->withHeaders([
            Header::EVENT_TYPE => RegisterCustomer::class,
        ]);
    }

    private function updateEmailCustomer(): object
    {
        return UpdateCustomerEmail::fromContent([])->withHeaders([
            Header::EVENT_TYPE => UpdateCustomerEmail::class,
        ]);
    }

    private function makeOrder(): object
    {
        return MakeOrder::fromContent([])->withHeaders([
            Header::EVENT_TYPE => MakeOrder::class,
        ]);
    }
}
