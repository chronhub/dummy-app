<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Domain\Command\MakeOrder;
use App\Chron\Domain\Command\RegisterCustomer;
use App\Chron\Domain\Command\UpdateCustomerEmail;
use App\Chron\Reporter\Manager\Manager;
use App\Chron\Reporter\Report;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final class HomeController
{
    use QueryPromiseTrait;

    public function __invoke(Manager $manager): Response
    {
        dd(app('reporter.command.mine'));

        $this->sendCommand(Report::command());

        return new Response('ok');
    }

    private function sendCommand(Reporter $reporter): void
    {
        $works = [
            fn () => $this->registerCustomer($reporter),
            fn () => $this->updateEmailCustomer($reporter),
            fn () => $this->makeOrder($reporter),
        ];

        $works[array_rand($works)]();
    }

    private function registerCustomer(Reporter $reporter): void
    {
        $command = RegisterCustomer::with(Uuid::v4()->jsonSerialize(), fake()->name, fake()->email);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => RegisterCustomer::class,
        ]);

        $reporter->relay($command);
    }

    private function updateEmailCustomer(Reporter $reporter): void
    {
        $command = UpdateCustomerEmail::fromContent([]);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => UpdateCustomerEmail::class,
        ]);

        $reporter->relay($command);
    }

    private function makeOrder(Reporter $reporter): void
    {
        $command = MakeOrder::fromContent([]);
        $command = $command->withHeaders([
            Header::EVENT_TYPE => MakeOrder::class,
        ]);

        $reporter->relay($command);
    }
}
