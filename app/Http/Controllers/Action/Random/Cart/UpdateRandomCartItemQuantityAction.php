<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Random\Cart;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

final readonly class UpdateRandomCartItemQuantityAction
{
    public function __invoke(): Response
    {
        $response = Http::acceptJson()
            ->asJson()
            ->post('chronhub.dvl.to/api/cart/update');

        return new Response('ok');
    }
}
