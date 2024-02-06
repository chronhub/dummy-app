<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Model\Customer\Command\ChangeCustomerEmail;
use App\Chron\Model\Customer\Command\RegisterCustomer;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Command\CompleteOrder;
use App\Chron\Reporter\Report;
use Illuminate\Database\Connection;
use Storm\Stream\StreamName;
use Storm\Support\QueryPromiseTrait;
use Symfony\Component\HttpFoundation\Response;

use function array_rand;
use function json_decode;

final class HomeController
{
    use QueryPromiseTrait;

    const CUSTOMER_ID = '11f6c9df-a2e2-3f56-a315-6d886c935a90';

    public function __invoke(): Response
    {
        $rand = [
            fn () => $this->registerCustomer(),
            fn () => $this->changeCustomerEmail(),
            fn () => $this->completeOrder(),
        ];

        $rand[array_rand($rand)]();

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
        $customerId = $this->findRandomCustomer();

        $command = ChangeCustomerEmail::withCustomer($customerId, fake()->email);

        Report::relay($command);
    }

    protected function completeOrder(): void
    {
        $order = $this->findPendingOrder();

        if ($order === null) {
            return;
        }

        $command = CompleteOrder::forCustomer($order['order_id'], $order['customer_id']);

        Report::relay($command);
    }

    protected function findRandomCustomer(): string
    {
        /** @var Connection $connection */
        $connection = app('db.connection');

        $customer = $connection->table('customer')->inRandomOrder()->first();

        return $customer->id;
    }

    protected function findPendingOrder(): ?array
    {
        /** @var Connection $connection */
        $connection = app('db.connection');

        $count = 0;
        while (true) {
            $order = $connection->table('order')->inRandomOrder()->first();

            $content = json_decode($order->content, true);

            $status = $content['order_status'] ?? null;

            if ($status === 'created') {
                return $content;
            }

            if ($count > 10) {
                logger('No pending order found');

                break;
            }

            $count++;
        }

        return null;
    }
}
