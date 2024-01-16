<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Domain\Command\MakeOrder;
use App\Chron\Domain\Command\RegisterCustomer;
use App\Chron\Domain\Command\UpdateCustomerEmail;
use App\Chron\Domain\Query\GetOneRandomCustomer;
use Storm\Contract\Message\Header;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportQuery;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final class HomeController
{
    use QueryPromiseTrait;

    public function __invoke(ReportCommand $reporter, ReportQuery $reportQuery): Response
    {
        $query = new GetOneRandomCustomer();

        $promise = $reportQuery->relay($query);

        dd($this->handle($promise));

        return new Response('ok');

        $works = [
            //fn () => $this->registerCustomer($reporter),
            //fn () => $this->updateEmailCustomer($reporter),
            fn () => $this->makeOrder($reporter),
        ];

        $works[array_rand($works)]();

        return new Response('ok');
    }

    private function registerCustomer(ReportCommand $reporter): void
    {
        $command = RegisterCustomer::with(Uuid::v4()->jsonSerialize(), fake()->name, fake()->email);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => RegisterCustomer::class,
        ]);

        $reporter->relay($command);
    }

    private function updateEmailCustomer(ReportCommand $reporter): void
    {
        $command = UpdateCustomerEmail::fromContent([]);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => UpdateCustomerEmail::class,
        ]);

        $reporter->relay($command);
    }

    private function makeOrder(ReportCommand $reporter): void
    {
        $command = MakeOrder::fromContent([]);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => MakeOrder::class,
        ]);

        $reporter->relay($command);
    }
}
