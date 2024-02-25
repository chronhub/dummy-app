<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Catalog;

use App\Chron\Projection\Provider\ProductProvider;
use Illuminate\View\View;

final class CatalogView
{
    public function __invoke(ProductProvider $productProvider): View
    {
        $products = $productProvider->getPaginatedProducts(15);

        return view('section.product.list')->with('products', $products);
    }
}
