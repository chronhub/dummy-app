<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Catalog;

use Illuminate\View\View;

final class CatalogView
{
    public function __invoke(): View
    {
        return view('product_list');
    }
}
