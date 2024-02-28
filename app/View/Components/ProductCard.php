<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use stdClass;

class ProductCard extends Component
{
    public function __construct(
        public string $type,
        public stdClass $product,
        public stdClass $cart,
    ) {
        //
    }

    public function isInCart(string $sku): bool
    {
        foreach ($this->cart->items as $item) {
            if ($item->sku_id === $sku) {
                return true;
            }
        }

        return false;
    }

    public function cartItemQuantity(string $sku): int
    {
        foreach ($this->cart->items as $item) {
            if ($item->sku_id === $sku) {
                return $item->quantity;
            }
        }

        return 0;
    }

    public function cartItemIdOfSku(string $sku): string
    {
        foreach ($this->cart->items as $item) {
            if ($item->sku_id === $sku) {
                return $item->id;
            }
        }

        return '';
    }

    public function render(): View|Closure|string
    {
        return view('components.product_card');
    }
}
