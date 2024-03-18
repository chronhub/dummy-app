<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public function __construct(
        public string $route,
        public string $label,
        public string $color = 'violet',
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.button');
    }
}
