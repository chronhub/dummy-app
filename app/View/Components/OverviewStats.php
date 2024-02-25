<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OverviewStats extends Component
{
    public function __construct(
        public string $label,
        public string $value,
        public string $path,
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.overview_stats');
    }
}
