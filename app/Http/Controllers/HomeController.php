<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Domain\Command\MakeOrder;
use App\Chron\Domain\Command\RegisterCustomer;
use App\Chron\Domain\Command\UpdateCustomerEmail;
use Storm\Contract\Message\Header;
use Storm\Reporter\ReportCommand;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function array_rand;

final class HomeController
{
    use QueryPromiseTrait;

    public function __invoke(): Response
    {
        $reporter = app('reporter.command.default');

        $this->sendCommand($reporter);

        return new Response('ok');
    }

    private function sendCommand(ReportCommand $reporter): void
    {
        $works = [
            fn () => $this->registerCustomer($reporter),
            fn () => $this->updateEmailCustomer($reporter),
            fn () => $this->makeOrder($reporter),
        ];

        $works[array_rand($works)]();
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
