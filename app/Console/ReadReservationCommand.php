<?php

declare(strict_types=1);

namespace App\Console;

use App\Chron\Model\Cart\Event\CartSubmitted;
use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Inventory\Event\InventoryItemReleased;
use App\Chron\Model\Inventory\Event\InventoryItemReserved;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\QueryProjectorScope;
use Storm\Contract\Projector\StreamNameAwareQueryFilter;

use function array_keys;
use function microtime;

class ReadReservationCommand extends Command
{
    protected $signature = 'read:reservation';

    protected bool $stop = false;

    public function __construct(private readonly ProjectorManagerInterface $projectorManager)
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $projection = $this->projectorManager->newQueryProjector();

        $start = microtime(true);

        $projection
            ->initialize(fn (): array => ['initial_stock' => 0, 'sold_stock' => 0, 'order_quantity' => 0, 'inventory_reserved' => 0, 'cart_submitted' => 0])
            ->subscribeToStream('inventory', 'cart', 'order')
            //->filter($this->projectorManager->queryScope()->fromIncludedPosition())
            ->filter($this->queryFilter())
            ->when($this->reactors())
            ->keepState()
            ->run(false);

        $end = microtime(true) - $start;

        $this->info("Time: $end");

        $queryState = $projection->getState();

        $this->table(array_keys($queryState), [
            [
                $queryState['initial_stock'],
                $queryState['sold_stock'],
                $queryState['order_quantity'],
                $queryState['inventory_reserved'],
                $queryState['cart_submitted'],
            ],
        ]);

        return self::SUCCESS;
    }

    private function reactors(): Closure
    {
        return function (QueryProjectorScope $scope): void {
            $scope
                ->ack(InventoryItemAdded::class)
                ?->incrementState('initial_stock', $scope->event()->totalStock()->value);

            $scope
                ->ack(InventoryItemReserved::class)
                ?->incrementState('inventory_reserved', $scope->event()->reserved()->value);

            $scope
                ->ack(InventoryItemReleased::class)
                ?->updateState('inventory_reserved', -($scope->event()->released()->value), true);

            $scope
                ->ack(OrderCreated::class)
                ?->incrementState('order_quantity', $scope->event()->orderItems()->calculateQuantity()->value);

            $scope
                ->ack(CartSubmitted::class)
                ?->incrementState('cart_submitted');

            $scope
                ->ack(OrderPaid::class)
                ?->incrementState('sold_stock', $scope->event()->orderQuantity()->value)
                ?->updateState('cart_submitted', -1, true);

        };
    }

    private function queryFilter(): ProjectionQueryFilter
    {
        return new class() implements ProjectionQueryFilter, StreamNameAwareQueryFilter
        {
            private const string FIELD = 'header->__event_type';

            private int $streamPosition;

            private string $streamName;

            public function apply(): callable
            {
                return function (Builder $query): void {
                    $query->where('position', '>=', $this->streamPosition);

                    if ($this->streamName === 'inventory') {
                        $query
                            ->whereJsonContains(self::FIELD, InventoryItemAdded::class)
                            ->orWhereJsonContains(self::FIELD, InventoryItemReserved::class)
                            ->orWhereJsonContains(self::FIELD, InventoryItemReleased::class);
                    }

                    if ($this->streamName === 'order') {
                        $query
                            ->whereJsonContains(self::FIELD, OrderCreated::class)
                            ->orWhereJsonContains(self::FIELD, OrderPaid::class);
                    }

                    if ($this->streamName === 'cart') {
                        $query
                            ->whereJsonContains(self::FIELD, CartSubmitted::class);
                    }

                    $query->orderBy('position');
                };
            }

            public function setStreamPosition(int $streamPosition): void
            {
                $this->streamPosition = $streamPosition;
            }

            public function setStreamName(string $streamName): void
            {
                $this->streamName = $streamName;
            }
        };
    }
}
