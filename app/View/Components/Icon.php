<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Icon extends Component
{
    public function __construct(
        public string $path,
        public string $label = 'default',
        public string $color = 'text-gray-800',
        public string $dark = 'text-white',
        public ?string $secondPath = null,
        public string $width = '6',
        public string $height = '6',
        public string $viewBox = '0 0 24 24',
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.icon');
    }
}
