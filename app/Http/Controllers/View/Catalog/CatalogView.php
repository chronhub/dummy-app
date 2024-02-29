<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Catalog;

use App\Chron\Application\Messaging\Query\QueryPaginatedProducts;
use App\Chron\Package\Reporter\Report;
use App\Chron\Package\Support\QueryPromiseTrait;
use App\Chron\Projection\Provider\ProductProvider;
use Illuminate\View\View;
use Throwable;

final class CatalogView
{
    use QueryPromiseTrait;

    public function __invoke(ProductProvider $productProvider): View
    {
        try {
            $products = $this->handlePromise(
                Report::relay(new QueryPaginatedProducts(10))
            );
        } catch (Throwable $e) {
            report($e);

            abort(501);
        }

        return view('section.product.list')->with('products', $products);
    }
}
