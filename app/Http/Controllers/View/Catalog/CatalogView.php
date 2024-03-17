<?php

declare(strict_types=1);

namespace App\Http\Controllers\View\Catalog;

use App\Chron\Application\Service\CatalogApplicationService;
use Illuminate\View\View;
use Throwable;

final class CatalogView
{
    public function __invoke(CatalogApplicationService $catalogApplicationService): View
    {
        try {
            $catalog = $catalogApplicationService->readCatalog();
        } catch (Throwable $e) {
            report($e);

            abort(501);
        }

        return view('section.catalog.list')->with('catalog', $catalog);
    }
}
