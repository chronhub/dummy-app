<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Cart\Cart;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\Repository\CartList;
use App\Chron\Package\Aggregate\Contract\AggregateRepository;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use App\Chron\Package\Attribute\AggregateRepository\AsAggregateRepository;

#[AsAggregateRepository(
    chronicler: 'chronicler.event.transactional.standard.pgsql',
    streamName: 'cart',
    aggregateRoot: Cart::class,
    messageDecorator: 'event.decorator.chain.default'
)]
final readonly class CartAggregateRepository implements CartList
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(CartId $cartId): ?Cart
    {
        /** @var AggregateRoot&Cart $aggregate */
        $aggregate = $this->repository->retrieve($cartId);

        return $aggregate;
    }

    public function save(Cart $cart): void
    {
        $this->repository->store($cart);
    }
}
