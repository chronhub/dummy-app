<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Cart\QueryAllNonEmptyOpenedCarts;
use App\Chron\Application\Messaging\Command\Cart\QueryRandomOpenCart;
use App\Chron\Application\Messaging\Command\Catalog\QueryProductFromCatalog;
use App\Chron\Application\Messaging\Command\Catalog\QueryRandomAvailableProductFromCatalog;
use App\Chron\Model\DomainException;
use Illuminate\Support\LazyCollection;
use stdClass;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;

/**
 * temporary placeholder for random queries
 */
final class ShopApplicationService
{
    use QueryPromiseTrait;

    /**
     * @throws DomainException when no open cart found
     */
    public function queryRandomOpenCart(): stdClass
    {
        $query = new QueryRandomOpenCart();

        $cart = $this->query($query);

        if (! $cart instanceof stdClass) {
            throw new DomainException('No open cart found');
        }

        return $cart;
    }

    /**
     * @throws DomainException when no available product found
     */
    public function queryRandomAvailableProductFromCatalog(): stdClass
    {
        $query = new QueryRandomAvailableProductFromCatalog();

        $product = $this->query($query);

        if (! $product instanceof stdClass) {
            throw new DomainException('No available product found');
        }

        return $product;
    }

    /**
     * @throws DomainException when no available product found
     */
    public function queryProductFromCatalog(string $sku): stdClass
    {
        $query = new QueryProductFromCatalog($sku);

        $product = $this->query($query);

        if (! $product instanceof stdClass) {
            throw new DomainException('No available product found');
        }

        return $product;
    }

    public function queryAllNonEmptyOpenedCarts(): LazyCollection
    {
        $query = new QueryAllNonEmptyOpenedCarts();

        return $this->query($query);
    }

    private function query(object $query): mixed
    {
        return $this->handlePromise(Report::relay($query));
    }
}
